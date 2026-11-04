"""Start an isolated local copy of Library KAIT without a MySQL server."""
import argparse
import json
import os
from pathlib import Path
import shutil
import socket
import sqlite3
import subprocess
import sys
import time
import urllib.error
import urllib.request
import webbrowser


ROOT = Path(__file__).resolve().parents[2]
TABLES = {"users", "genres", "books", "copies", "loans", "reservations", "reviews"}


def find_php(requested):
    candidates = [requested] if requested else [shutil.which("php")]
    if not requested and os.name == "nt":
        candidates += [r"C:\xampp\php\php.exe", r"C:\php\php.exe"]
    for candidate in candidates:
        if candidate and Path(candidate).is_file():
            return str(Path(candidate).resolve())
    raise RuntimeError("PHP не найден. Установите PHP 8 с PDO SQLite и mbstring или XAMPP в C:\\xampp.")


def check_php(php):
    result = subprocess.run(
        [php, "-r", "echo json_encode(['version'=>PHP_VERSION_ID,'extensions'=>get_loaded_extensions()]);"],
        capture_output=True, text=True, timeout=15, check=False,
    )
    if result.returncode:
        raise RuntimeError("Не удалось запустить PHP. Проверьте его установку и Visual C++ Runtime.")
    try:
        info = json.loads(result.stdout)
    except (ValueError, TypeError) as error:
        raise RuntimeError("PHP вернул некорректный ответ проверки расширений.") from error
    if info["version"] < 80000:
        raise RuntimeError("Для приложения требуется PHP 8 или новее.")
    missing = {"PDO", "pdo_sqlite", "mbstring", "session", "json", "filter"} - set(info["extensions"])
    if missing:
        raise RuntimeError("В PHP отсутствуют расширения: " + ", ".join(sorted(missing)) + ". См. practice/m01/windows-start.md.")


def prepare_database(data_dir, port):
    env_file = ROOT / ".env"
    existing = env_file.exists()
    if existing:
        config = {}
        for line in env_file.read_text(encoding="utf-8-sig").splitlines():
            key, sep, value = line.partition("=")
            if sep and not key.lstrip().startswith("#"):
                config[key.strip()] = value.strip().strip("\"'")
        if config.get("DB_DRIVER", "mysql") != "sqlite":
            raise RuntimeError("В .env уже настроена другая БД. Файл сохранён. Для этого запуска используйте отдельную копию проекта без .env.")
        database = Path(config.get("DB_NAME", ""))
        if not database.is_absolute():
            raise RuntimeError("В существующем .env параметр DB_NAME должен быть абсолютным путём к SQLite.")
    else:
        database = data_dir.expanduser().resolve() / "library.sqlite"
    if not database.exists():
        result = subprocess.run([sys.executable, str(ROOT / "practice/m01/init-sqlite.py"), str(database)], check=False)
        if result.returncode:
            raise RuntimeError("Создание тестовой БД не завершилось.")
    with sqlite3.connect(database.as_uri() + "?mode=ro", uri=True) as db:
        present = {row[0] for row in db.execute("SELECT name FROM sqlite_master WHERE type='table'")}
        if not TABLES.issubset(present):
            raise RuntimeError("SQLite не содержит всех таблиц библиотеки. Существующие данные сохранены.")
    if not existing:
        content = (f'APP_NAME="Библиотека КАИТ"\nAPP_URL=http://127.0.0.1:{port}\n'
                   f'APP_DEBUG=false\nDB_DRIVER=sqlite\nDB_NAME="{database.as_posix()}"\n')
        with env_file.open("x", encoding="utf-8", newline="\n") as stream:
            stream.write(content)
    return database


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--php", help="Full path to php.exe")
    parser.add_argument("--port", type=int, default=8080)
    parser.add_argument("--data-dir", type=Path, default=Path.home() / ".kait-library")
    parser.add_argument("--no-browser", action="store_true")
    args = parser.parse_args()
    if not 1024 <= args.port <= 65535:
        raise RuntimeError("Порт должен быть от 1024 до 65535.")
    php = find_php(args.php)
    check_php(php)
    with socket.socket() as probe:
        try:
            probe.bind(("127.0.0.1", args.port))
        except OSError as error:
            raise RuntimeError("Порт занят. Закройте предыдущий сервер или укажите --port 8081.") from error
    database = prepare_database(args.data_dir, args.port)
    address = f"http://127.0.0.1:{args.port}/"
    print("Тестовый стенд SQLite. Данные:", database, flush=True)
    server = subprocess.Popen([php, "-S", f"127.0.0.1:{args.port}", "-t", str(ROOT / "public")], cwd=ROOT)
    try:
        for attempt in range(50):
            if server.poll() is not None:
                raise RuntimeError("PHP-сервер завершился при запуске. См. сообщение в этом окне.")
            try:
                with urllib.request.urlopen(address, timeout=1) as response:
                    response.read()
                    break
            except urllib.error.HTTPError as error:
                raise RuntimeError("Сайт вернул HTTP " + str(error.code) + ". Проверьте настройки .env.") from error
            except (urllib.error.URLError, TimeoutError, ConnectionError):
                time.sleep(0.1)
        else:
            raise RuntimeError("Сервер не ответил после запуска.")
        try:
            with urllib.request.urlopen(address + "api.php?action=books.search", timeout=5) as response:
                catalog = json.load(response)
            if not catalog.get("ok") or not isinstance(catalog.get("items"), list):
                raise ValueError("Catalog API failed")
        except (ValueError, urllib.error.URLError, TimeoutError) as error:
            raise RuntimeError("Страница открылась, но каталог не загрузился. Проверьте подключение к SQLite.") from error
        print("Библиотека открыта:", address, "\nДля остановки нажмите Ctrl+C.", flush=True)
        if not args.no_browser:
            webbrowser.open(address)
        server.wait()
        return server.returncode
    except KeyboardInterrupt:
        print("\nСервер остановлен.")
        return 0
    finally:
        if server.poll() is None:
            server.terminate()
            try:
                server.wait(timeout=5)
            except subprocess.TimeoutExpired:
                server.kill()
                server.wait()


if __name__ == "__main__":
    if hasattr(sys.stdout, "reconfigure"):
        sys.stdout.reconfigure(encoding="utf-8")
    try:
        raise SystemExit(main())
    except (RuntimeError, OSError, subprocess.TimeoutExpired) as error:
        print("Ошибка:", error, file=sys.stderr)
        raise SystemExit(1)
