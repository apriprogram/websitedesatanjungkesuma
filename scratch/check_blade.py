
import re

with open(r'c:\Users\ASUS\Videos\WEB DEVELOPER\WEBSITE DESA TANJUNG KESUMA OK\Website Tanjung Kesuma\resources\views\admin\dashboard.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

directives = ['if', 'foreach', 'forelse', 'section', 'push', 'php']
for d in directives:
    opens = len(re.findall(f'@{d}', content))
    closes = len(re.findall(f'@end{d}', content))
    print(f'{d}: opens={opens}, closes={closes}')

# Also check for @empty in @forelse
forelse_count = len(re.findall('@forelse', content))
empty_count = len(re.findall('@empty', content))
print(f'forelse: {forelse_count}, empty: {empty_count}')
