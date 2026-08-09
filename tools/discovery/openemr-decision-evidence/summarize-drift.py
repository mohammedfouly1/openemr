import csv
from collections import Counter, defaultdict
csv_path = r'D:\OpenEmr\docs\discovery\openemr-decision-evidence\07-core-modification-inventory.csv'
with open(csv_path, encoding='utf-8') as fh:
    rows = list(csv.DictReader(fh))

cls = Counter(r['classification'] for r in rows)
print('CLASSIFICATION:')
for k,v in sorted(cls.items(), key=lambda kv:-kv[1]):
    pct = 100*v/len(rows)
    print(f'  {k}: {v} ({pct:.1f}%)')

ct = Counter(r['change_type'] for r in rows)
print('\nCHANGE-TYPE:')
for k,v in sorted(ct.items()): print(f'  {k}: {v}')

def top(p):
    return p.split('/',1)[0] if '/' in p else '(root)'
by_dir = defaultdict(lambda: {'files':0,'add':0,'del':0})
for r in rows:
    t = top(r['path'])
    by_dir[t]['files'] += 1
    try: by_dir[t]['add'] += int(r['added_lines'])
    except: pass
    try: by_dir[t]['del'] += int(r['deleted_lines'])
    except: pass
print('\nTOP-LEVEL DIR:')
for d in sorted(by_dir, key=lambda k:-by_dir[k]['files']):
    v = by_dir[d]
    print(f"  {d}: files={v['files']} +{v['add']} -{v['del']}")

adds = [r['path'] for r in rows if r['change_type']=='A']
dels = [r['path'] for r in rows if r['change_type']=='D']
rens = [r for r in rows if r['change_type'].startswith('R')]
print(f'\nA: {len(adds)}  D: {len(dels)}  R: {len(rens)}')
print('DELETED:')
for d in dels: print(f'  {d}')
print('RENAMED:')
for r in rens: print(f"  {r['change_type']} {r['path']}")
add_by_dir = Counter(top(p) for p in adds)
print('\nADDED by top-level:')
for k,v in sorted(add_by_dir.items(), key=lambda kv:-kv[1]): print(f'  {k}: {v}')

# second-level for interface/
sec = Counter()
for r in rows:
    parts = r['path'].split('/')
    if len(parts)>=2:
        sec[f'{parts[0]}/{parts[1]}'] += 1
print('\nTOP-20 SECOND-LEVEL:')
for k,v in sorted(sec.items(), key=lambda kv:-kv[1])[:20]:
    print(f'  {k}: {v}')
