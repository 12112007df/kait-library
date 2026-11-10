"""Repeat isolated HTTP controls against a disposable SQLite database.
Run with --base BASE.sqlite --database DB.sqlite --url URL --label before|after.
Uses synthetic seed accounts only. Never point --database at a live database.
"""
import argparse, http.cookiejar, json, pathlib, shutil, sqlite3, urllib.request, urllib.parse
p=argparse.ArgumentParser(description=__doc__)
for k in ('base','database','url','label'): p.add_argument('--'+k,required=True)
a=p.parse_args()
with sqlite3.connect(a.base) as seed_db:
 fixture_emails=dict(seed_db.execute('SELECT id,email FROM users'))
student=fixture_emails[6]; teacher=fixture_emails[5]
cases=[('student',student,'INV-0003',''),('teacher_overdue',teacher,'INV-0006',''),
 ('issued',student,'INV-0001',''),('reserved',student,'INV-0003','reserve'),
 ('unknown_reader','missing@example.test','INV-0003',''),('blocked',student,'INV-0003','blocked'),
 ('unknown_copy',student,'missing',''),('repair',student,'INV-0010',''),
 ('lost',student,'INV-0003','lost'),('student_overdue',fixture_emails[3],'INV-0003',''),
 ('student_at_limit',student,'INV-0003','limit5'),('student_over_limit',student,'INV-0003','limit6'),
 ('own_reservation',student,'INV-0003','own'),('queue_with_spare',student,'INV-0003','spare')]
results=[]
for name,email,inventory,setup in cases:
 shutil.copyfile(a.base,a.database)
 with sqlite3.connect(a.database) as db:
  if setup=='blocked': db.execute('UPDATE users SET is_blocked=1 WHERE id=6')
  if setup=='lost': db.execute("UPDATE copies SET status='lost' WHERE inventory_number='INV-0003'")
  if setup in ('reserve','own','spare'):
   db.execute("INSERT INTO reservations(book_id,user_id,created_at,status) VALUES(2,?,'2026-10-01 09:00:00','active')",(6 if setup=='own' else 5,))
  if setup=='spare': db.execute("INSERT INTO copies(book_id,inventory_number,status) VALUES(2,'M03-SPARE','available')")
  if setup.startswith('limit'):
   for i in range(int(setup[5:])):
    cid=db.execute("INSERT INTO copies(book_id,inventory_number,status) VALUES(1,?,'issued')",('M03-LIMIT-'+str(i),)).lastrowid
    db.execute("INSERT INTO loans(copy_id,user_id,librarian_id,issued_at,due_at) VALUES(?,6,2,'2026-11-01','2099-01-01')",(cid,))
 opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
 login=urllib.request.Request(a.url+'/index.php?page=login',data=urllib.parse.urlencode({'email':fixture_emails[2],'password':'password'}).encode())
 with opener.open(login) as r: assert r.status==200
 req=urllib.request.Request(a.url+'/api.php?action=loan.issue',data=json.dumps({'email':email,'inventory':inventory}).encode(),headers={'Content-Type':'application/json'})
 try: response=opener.open(req)
 except urllib.error.HTTPError as e: response=e
 raw=response.read().decode(); payload=json.loads(raw)
 with sqlite3.connect(a.database) as db:
  state={table:[list(row) for row in db.execute('SELECT * FROM '+table+' ORDER BY id').fetchall()] for table in ('loans','copies','reservations')}
 result={'scenario':name,'inventory':inventory,'http':response.status,'body':payload,'raw':raw,'database':state}
 results.append(result)
 print(name,response.status,payload)
# Each response, including warning order and all DB mutations, must match exactly.
out=pathlib.Path(__file__).with_name('control-'+a.label+'.json')
out.write_text(json.dumps(results,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
if a.label=='after':
 before=json.loads(out.with_name('control-before.json').read_text(encoding='utf-8'))
 assert results==before,'HTTP response or database state changed'
 print('PASS: 14/14 scenarios match before, including raw JSON and full database state')
shutil.copyfile(a.base,a.database)
