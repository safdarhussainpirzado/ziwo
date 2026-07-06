import base64
import zlib
import re

with open('database/seeders/GeospatialMarkerSeeder.php', 'r') as f:
    content = f.read()

match = re.search(r"\$encodedData = '(.*?)';", content, re.S)
if match:
    encoded = match.group(1).replace('\n', '').replace(' ', '').replace("'", "")
    decoded = zlib.decompress(base64.b64decode(encoded), 16+zlib.MAX_WBITS).decode()
    lines = decoded.split('\n')
    for line in lines[:10]:
        print(line)
else:
    print("Not found")
