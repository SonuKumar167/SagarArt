<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['export']) && $_GET['export'] === '1') {
    $exportResult = $conn->query('SELECT id, name, email, phone, message, submitted_at FROM contact_submissions ORDER BY submitted_at DESC');
    $rows = [];
    $rows[] = ['ID', 'Name', 'Email', 'Phone', 'Message', 'Submitted At'];
    while ($row = $exportResult->fetch_assoc()) {
        $rows[] = [$row['id'], $row['name'], $row['email'], $row['phone'] ?? '', $row['message'], $row['submitted_at']];
    }

    $filename = 'contact-messages-' . date('Ymd_His') . '.xlsx';
    if (!class_exists('ZipArchive')) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . str_replace('.xlsx', '.csv', $filename) . '"');
        $output = fopen('php://output', 'w');
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
    $zip = new ZipArchive();
    if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        header('HTTP/1.1 500 Internal Server Error');
        exit('Unable to create XLSX export.');
    }

    $contentTypes = '<?xml version="1.0" encoding="UTF-8"?>\n' .
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">\n' .
        '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>\n' .
        '  <Default Extension="xml" ContentType="application/xml"/>\n' .
        '  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>\n' .
        '  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>\n' .
        '  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>\n' .
        '</Types>';

    $rels = '<?xml version="1.0" encoding="UTF-8"?>\n' .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">\n' .
        '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="/xl/workbook.xml"/>\n' .
        '</Relationships>';

    $workbook = '<?xml version="1.0" encoding="UTF-8"?>\n' .
        '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">\n' .
        '  <sheets>\n' .
        '    <sheet name="Messages" sheetId="1" r:id="rId1"/>\n' .
        '  </sheets>\n' .
        '</workbook>';

    $workbookRels = '<?xml version="1.0" encoding="UTF-8"?>\n' .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">\n' .
        '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>\n' .
        '  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>\n' .
        '</Relationships>';

    $styles = '<?xml version="1.0" encoding="UTF-8"?>\n' .
        '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">\n' .
        '  <fonts count="1">\n' .
        '    <font>\n' .
        '      <sz val="11"/>\n' .
        '      <color theme="1"/>\n' .
        '      <name val="Calibri"/>\n' .
        '      <family val="2"/>\n' .
        '    </font>\n' .
        '  </fonts>\n' .
        '  <fills count="2">\n' .
        '    <fill>\n' .
        '      <patternFill patternType="none"/>\n' .
        '    </fill>\n' .
        '    <fill>\n' .
        '      <patternFill patternType="gray125"/>\n' .
        '    </fill>\n' .
        '  </fills>\n' .
        '  <borders count="1">\n' .
        '    <border>\n' .
        '      <left/>\n' .
        '      <right/>\n' .
        '      <top/>\n' .
        '      <bottom/>\n' .
        '      <diagonal/>\n' .
        '    </border>\n' .
        '  </borders>\n' .
        '  <cellStyleXfs count="1">\n' .
        '    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>\n' .
        '  </cellStyleXfs>\n' .
        '  <cellXfs count="1">\n' .
        '    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>\n' .
        '  </cellXfs>\n' .
        '  <cellStyles count="1">\n' .
        '    <cellStyle name="Normal" xfId="0" builtinId="0"/>\n' .
        '  </cellStyles>\n' .
        '</styleSheet>';

    $sheetData = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
        '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">\n' .
        '  <sheetData>\n';

    foreach ($rows as $rowIndex => $row) {
        $sheetData .= '    <row r="' . ($rowIndex + 1) . '">\n';
        foreach ($row as $colIndex => $cellValue) {
            $col = chr(65 + $colIndex);
            $sheetData .= '      <c r="' . $col . ($rowIndex + 1) . '" t="inlineStr">\n';
            $sheetData .= '        <is><t>' . htmlspecialchars((string) $cellValue, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is>\n';
            $sheetData .= '      </c>\n';
        }
        $sheetData .= '    </row>\n';
    }

    $sheetData .= '  </sheetData>\n</worksheet>';

    $zip->addFromString('[Content_Types].xml', $contentTypes);
    $zip->addFromString('_rels/.rels', $rels);
    $zip->addFromString('xl/workbook.xml', $workbook);
    $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
    $zip->addFromString('xl/styles.xml', $styles);
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheetData);
    $zip->close();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($tempFile);
    unlink($tempFile);
    exit;
}

$submissions = $conn->query('SELECT id, name, email, phone, message, submitted_at FROM contact_submissions ORDER BY submitted_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Submissions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-shell">
  <div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="admin-main">
      <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h3 class="fw-bold mb-1">Contact Messages</h3>
            <p class="text-muted mb-0">Review all submitted inquiries from the website.</p>
          </div>
          <div class="d-flex gap-2">
            <a href="?export=1" class="btn btn-success">Export XLSX</a>
            <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
          </div>
        </div>
        <div class="card admin-card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Submitted</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $submissions->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['name']); ?></td>
                      <td><?php echo htmlspecialchars($row['email']); ?></td>
                      <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                      <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                      <td><?php echo htmlspecialchars($row['submitted_at']); ?></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
<?php $conn->close(); ?>
