import os
import glob
import re

files = glob.glob('resources/views/admin/*/index.blade.php')

# We want to replace `<div x-data='someManager({ ... })'` with `<div x-data="someManager({ ... })"`
# And also replace `@json(...)` with `{{ json_encode(...) }}` inside the x-data block

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Simple strategy: find x-data='...Manager({ ... })' class="...
    # We'll use a regex to capture everything from x-data='...Manager({ up to })'
    
    pattern = re.compile(r"x-data='(\w+Manager\(.*?\))'", re.DOTALL)
    
    def replacer(match):
        inner = match.group(1)
        # replace @json($var) with {{ json_encode($var) }}
        inner = re.sub(r'@json\((.*?)\)', r'{{ json_encode(\1) }}', inner)
        return f'x-data="{inner}"'
    
    new_content = pattern.sub(replacer, content)
    
    if new_content != content:
        with open(file, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Fixed {file}")

