Article status [First Draft] requires [Editing] Add parameter descriptions
# File Helper
Provides methods for splitting and joining FILES.

### Split a file into segments.
The **file::split()** method accepts multiple parameters. Only the input **filename** is required.
Returns the number of file segments created.
<code>
$segments = file::split($filename, $output_directory = FALSE, $piece_size = 10);
</code>

### Join segments of a split file.
The **file::join()** method accepts multiple parameters. Only the input segment **filename** is required.
Returns the number of file segments joined.
<code>
$segments = file::split($filename, $output_file = FALSE);
</code>