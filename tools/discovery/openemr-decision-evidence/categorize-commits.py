import re, subprocess
from collections import Counter
from pathlib import Path

ROOT = Path(r'D:\OpenEmr')
log = (ROOT / 'docs/discovery/openemr-decision-evidence/evidence/raw/log-HEAD-to-upstream-master.txt').read_text(encoding='utf-8-sig').splitlines()
# Categorize
cat_re = re.compile(r'^\w+\s+(feat|fix|chore|refactor|docs|test|ci|build|perf|style|revert)')
cats = Counter()
top40 = []
for i, line in enumerate(log):
    m = cat_re.match(line)
    if m: cats[m.group(1)] += 1
    else: cats['(other/no-prefix)'] += 1
    if i < 40:
        top40.append(line)
print(f'Total commits HEAD..upstream/master: {len(log)}')
print('By conventional-commit type:')
for k,v in sorted(cats.items(), key=lambda kv:-kv[1]):
    print(f'  {k}: {v}')
print()
print('TOP-40 COMMITS:')
for l in top40: print(f'  {l}')

# v8_2_0 stats
print('\n--- v8_2_0 drift ---')
r = subprocess.run(['git','-C',str(ROOT),'diff','--name-status','HEAD','v8_2_0'], capture_output=True, text=True)
ct = Counter()
paths_by_status = {'A':[], 'D':[], 'M':[]}
for line in r.stdout.splitlines():
    parts = line.split('\t')
    s = parts[0][0]
    ct[parts[0]] += 1
    paths_by_status.setdefault(s, []).append(parts[-1])
for k,v in sorted(ct.items()): print(f'  {k}: {v}')
print('v8_2_0 A files:')
for p in paths_by_status.get('A', []): print(f'  A {p}')
print('v8_2_0 D files:')
for p in paths_by_status.get('D', []): print(f'  D {p}')
