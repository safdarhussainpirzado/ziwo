# Read the seeder
with open('database/seeders/GeospatialMarkerSeeder.php', 'r') as f:
    content = f.read()

# Add truncate logic
new_line = "        ini_set('memory_limit', '-1');\n        DB::table('geospatial_markers')->truncate();"
updated_content = content.replace("        ini_set('memory_limit', '-1');", new_line)

with open('database/seeders/GeospatialMarkerSeeder.php', 'w') as f:
    f.write(updated_content)
