"""Create an isolated SQLite smoke-test database from the original MySQL SQL files.

Usage: python practice/m01/init-sqlite.py /absolute/path/to/library.sqlite
MySQL/MariaDB remains the project's primary database; this helper only uses
the SQLite driver already implemented in src/Database.php. It never overwrites
an existing database or changes application source files.
"""
import pathlib
import re
import sqlite3
import sys


def main():
    if len(sys.argv) != 2:
        raise SystemExit(__doc__)
    target = pathlib.Path(sys.argv[1]).resolve()
    if target.exists():
        raise SystemExit(f"Database already exists: {target}")
    root = pathlib.Path(__file__).resolve().parents[2]
    schema = (root / "database/schema.sql").read_text(encoding="utf-8")
    seed = (root / "database/seed.sql").read_text(encoding="utf-8")
    schema = re.sub(r"^SET NAMES .*?;\s*", "", schema, flags=re.M)
    schema = re.sub(r"INT AUTO_INCREMENT PRIMARY KEY", "INTEGER PRIMARY KEY AUTOINCREMENT", schema)
    schema = re.sub(r"ENUM\([^)]*\)", "TEXT", schema)
    schema = re.sub(r"\) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", ");", schema)
    seed = re.sub(r"^SET NAMES .*?;\s*", "", seed, flags=re.M)
    target.parent.mkdir(parents=True, exist_ok=True)
    with sqlite3.connect(target) as db:
        db.execute("PRAGMA foreign_keys = ON")
        db.executescript(schema)
        db.executescript(seed)
        violations = db.execute("PRAGMA foreign_key_check").fetchall()
        if violations:
            raise RuntimeError(violations)
        counts = {table: db.execute(f"SELECT COUNT(*) FROM {table}").fetchone()[0]
                  for table in ("users", "genres", "books", "copies", "loans", "reservations", "reviews")}
    print(f"Created {target}")
    print(counts)
    print("SQLite does not reproduce MySQL ENUM, length limits or collation; this is a smoke-test database.")


if __name__ == "__main__":
    main()
