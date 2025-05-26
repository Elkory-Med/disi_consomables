CSS Cleanup - Backup Documentation
==============================

Date: April 14, 2025

Files backed up before removal:
------------------------------
1. dashboard-charts-style.css - Deprecated CSS that was replaced by the unified version
2. dashboard-charts.css - Deprecated CSS that was replaced by the unified version
3. chart-fix.css - Deprecated CSS that was replaced by the unified version

Reason for removal:
------------------
These files were causing warnings in the editor and were no longer used in the application.
They have been consolidated into dashboard-charts-unified.css which is the only CSS file
that should be used for chart styling.

How to restore (if needed):
--------------------------
If you need to restore these files (though this should not be necessary), copy them
from this backup directory to the parent directory:

```
Copy-Item -Path "public/css/backup/dashboard-charts-style.css" -Destination "public/css/" -Force
Copy-Item -Path "public/css/backup/dashboard-charts.css" -Destination "public/css/" -Force
Copy-Item -Path "public/css/backup/chart-fix.css" -Destination "public/css/" -Force
```

Note: Be aware that restoring these files might cause CSS conflicts since they contain
overlapping style definitions with dashboard-charts-unified.css 