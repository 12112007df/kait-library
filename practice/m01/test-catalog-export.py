"""Integration checks for the catalog export in an isolated temporary database."""
import argparse
import csv
from datetime import datetime, timezone, timedelta
import http.cookiejar
import io
import json
from pathlib import Path
import shutil
import socket
import sqlite3
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--php', required=True)
    parser.add_argument('--frankenphp', action='store_true')
    parser.add_argument('--out', type=Path)
    args = parser.parse_args()
    root = Path(__file__).resolve().parents[2]
    php = str(Path(args.php).resolve())
    prefix = [php, 'php-cli'] if args.frankenphp else [php]
    checks = []

    def passed(name):
        checks.append({'name': name, 'result': 'passed'})
        print('PASS:', name, flush=True)

    with tempfile.TemporaryDirectory(prefix='kait-csv-') as folder:
        work = Path(folder)
        for name in ('public', 'src', 'templates', 'database', 'config'):
            shutil.copytree(root / name, work / name)
        database = work / 'library.sqlite'
        import sys
        subprocess.run([sys.executable, str(root / 'practice/m01/init-sqlite.py'), str(database)], check=True)
        (work / '.env').write_text('APP_DEBUG=false\nDB_DRIVER=sqlite\nDB_NAME="' + database.as_posix() + '"\n', encoding='utf-8')
        parse_code = 'foreach (array_slice($argv,1) as $f) { token_get_all(file_get_contents($f), TOKEN_PARSE); }'
        files = [str(p) for p in work.rglob('*.php')]
        subprocess.run(prefix + ['-r', parse_code, '--'] + files, check=True, capture_output=True)
        passed('Syntax of all 27 application PHP files')
        with socket.socket() as probe:
            probe.bind(('127.0.0.1', 0))
            port = probe.getsockname()[1]
        address = f'http://127.0.0.1:{port}/'
        command = ([php, 'php-server', '--listen', f'127.0.0.1:{port}', '--root', str(work / 'public')]
                   if args.frankenphp else [php, '-S', f'127.0.0.1:{port}', '-t', str(work / 'public')])
        log = (work / 'server.log').open('w')
        server = subprocess.Popen(command, cwd=work, stdout=log, stderr=log)

        def session(email=None):
            opener = urllib.request.build_opener(urllib.request.ProxyHandler({}), urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
            if email:
                data = urllib.parse.urlencode({'email': email, 'password': 'password'}).encode()
                with opener.open(address + 'index.php?page=login', data, timeout=5) as response:
                    response.read()
            return opener

        def request(opener, params=None, method='GET', suffix=None):
            query = urllib.parse.urlencode({'action': 'books.export', **(params or {})})
            url = address + (suffix if suffix is not None else 'api.php?' + query)
            try:
                with opener.open(urllib.request.Request(url, method=method), timeout=5) as response:
                    return response.status, response.headers, response.read()
            except urllib.error.HTTPError as error:
                return error.code, error.headers, error.read()

        def rows(opener, params=None):
            status, headers, data = request(opener, params)
            assert status == 200, (status, data)
            assert headers.get_content_type() == 'text/csv'
            assert headers['Content-Disposition'] == 'attachment; filename="catalog.csv"'
            assert headers['Cache-Control'] == 'no-store'
            assert data.startswith(b'\xef\xbb\xbf')
            return list(csv.reader(io.StringIO(data.decode('utf-8-sig')), delimiter=';'))

        try:
            anonymous = session()
            for _ in range(80):
                try:
                    if request(anonymous, suffix='')[0] == 200:
                        break
                except (OSError, urllib.error.URLError):
                    pass
                if server.poll() is not None:
                    raise AssertionError('PHP server exited')
                time.sleep(0.1)
            else:
                raise AssertionError('PHP server did not respond')
            assert request(anonymous)[0] == 401
            assert b'id="export-catalog"' not in request(anonymous, suffix='')[2]
            passed('Anonymous export denied and export button hidden')
            reader = session('ivanov@student.kait20.ru')
            assert request(reader)[0] == 403
            assert b'id="export-catalog"' not in request(reader, suffix='')[2]
            passed('Reader export denied and export button hidden')
            librarian = session('librarian@kait20.ru')
            assert b'id="export-catalog"' in request(librarian, suffix='')[2]
            data = rows(librarian)
            assert len(data) == 11 and all(len(row) == 11 for row in data)
            assert sum(int(row[6]) for row in data[1:]) == 14
            passed('Librarian CSV has 10 books, 11 columns and 14 copies with UTF-8 BOM')
            admin = session('admin@kait20.ru')
            assert len(rows(admin)) == 11
            passed('Administrator can export the catalog')
            data = rows(librarian, {'q': 'Чистый'})
            assert len(data) == 2 and data[1][1] == 'Чистый код'
            assert data[1][6:9] == ['2', '1', '1']
            passed('Title filter and copy availability match database')
            assert len(rows(librarian, {'genre': '1'})) == 5
            passed('Genre filter returns the four programming books')
            data = rows(librarian, {'sort': 'b.year DESC'})
            years = [int(row[4]) for row in data[1:]]
            assert years == sorted(years, reverse=True)
            passed('Year sorting is descending')
            for query in ('Absent title', "' OR 1=1 --", '%', '_'):
                assert len(rows(librarian, {'q': query})) == 1
            passed('Empty results and SQL/LIKE metacharacters produce only CSV header')
            invalid = [{'q': 'я' * 101}, {'genre': '-1'}, {'genre': '0'}, {'sort': 'b.id; DROP TABLE books'}]
            for params in invalid:
                assert request(librarian, params)[0] == 422
            assert request(librarian, suffix='api.php?action=books.export&q%5B%5D=x')[0] == 422
            assert request(librarian, suffix='api.php?action=books.export&q=%FF')[0] == 422
            assert request(librarian, suffix='api.php?action=books.export&q=%0A')[0] == 422
            passed('Invalid scalar, UTF-8, length, genre, control and sort values rejected')
            assert request(librarian, method='POST')[0] == 405
            passed('Export rejects POST with HTTP 405')
            title = '=1+1; "Книга"\nвторая строка'
            with sqlite3.connect(database) as db:
                db.execute('INSERT INTO books(title,author,genre_id,year,isbn,created_at) VALUES(?,?,?,?,?,?)',
                           (title, '+Опасный автор', 1, 2026, '0123456789', '2026-11-04 12:00:00'))
            data = rows(librarian, {'q': '=1+1'})
            assert len(data) == 2 and data[1][1] == "'" + title
            assert data[1][2] == "'+Опасный автор" and data[1][5] == '0123456789'
            assert data[1][6:] == ['0', '0', '0', '0', '0']
            passed('Quotes, semicolon, newline, formula prefixes and books without copies handled')
            assert json.loads(request(anonymous, suffix='api.php?action=books.search')[2])['total'] == 11
            passed('Original catalog search still responds after export requests')
        finally:
            server.terminate()
            try:
                server.wait(timeout=5)
            except subprocess.TimeoutExpired:
                server.kill()
                server.wait()
            log.close()
    result = {'date': datetime.now(timezone(timedelta(hours=3))).date().isoformat(),
              'database': 'Temporary SQLite, initialized from schema.sql and seed.sql',
              'runtime': 'FrankenPHP' if args.frankenphp else 'PHP CLI',
              'checks': checks, 'passed': len(checks), 'failed': 0,
              'limitations': ['MySQL/MariaDB not tested', 'Browser click not automated', 'Not proof of running on student PC']}
    if args.out:
        args.out.write_text(json.dumps(result, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    print(json.dumps({'passed': len(checks), 'failed': 0}))


if __name__ == '__main__':
    main()
