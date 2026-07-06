<?php
require '../includes/config.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$settings = getSiteSettings($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name'] ?? 'Sagar Art');
    $tagline = trim($_POST['tagline'] ?? '');
    $header_text = trim($_POST['header_text'] ?? '');
    $header_cta_text = trim($_POST['header_cta_text'] ?? '');
    $header_cta_link = trim($_POST['header_cta_link'] ?? '');
    $footer_about = trim($_POST['footer_about'] ?? '');
    $footer_email = trim($_POST['footer_email'] ?? '');
    $footer_phone = trim($_POST['footer_phone'] ?? '');
    $footer_address = trim($_POST['footer_address'] ?? '');
    $footer_cta_heading = trim($_POST['footer_cta_heading'] ?? '');
    $footer_cta_text = trim($_POST['footer_cta_text'] ?? '');
    $footer_cta_button_text = trim($_POST['footer_cta_button_text'] ?? '');
    $footer_cta_button_link = trim($_POST['footer_cta_button_link'] ?? '');
    $footer_copyright = trim($_POST['footer_copyright'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $facebook_url = trim($_POST['facebook_url'] ?? '');
    $instagram_url = trim($_POST['instagram_url'] ?? '');
    $twitter_url = trim($_POST['twitter_url'] ?? '');
    $youtube_url = trim($_POST['youtube_url'] ?? '');

    $favicon_url = trim($_POST['existing_favicon_url'] ?? '');
    if (!empty($_FILES['favicon']['tmp_name'])) {
        $uploadedFavicon = uploadFile($_FILES['favicon']);
        if ($uploadedFavicon !== '') {
            $favicon_url = $uploadedFavicon;
        }
    }

    $logo_url = trim($_POST['existing_logo_url'] ?? '');
    if (!empty($_FILES['logo']['tmp_name'])) {
        $uploadedLogo = uploadFile($_FILES['logo']);
        if ($uploadedLogo !== '') {
            $logo_url = $uploadedLogo;
        }
    }

    $params = [$site_name, $tagline, $header_text, $header_cta_text, $header_cta_link, $footer_about, $footer_email, $footer_phone, $footer_address, $footer_cta_heading, $footer_cta_text, $footer_cta_button_text, $footer_cta_button_link, $footer_copyright, $favicon_url, $logo_url, $meta_title, $meta_description, $facebook_url, $instagram_url, $twitter_url, $youtube_url];
    $types = str_repeat('s', count($params));
    $stmt = $conn->prepare('INSERT INTO site_settings (site_name, tagline, header_text, header_cta_text, header_cta_link, footer_about, footer_email, footer_phone, footer_address, footer_cta_heading, footer_cta_text, footer_cta_button_text, footer_cta_button_link, footer_copyright, favicon_url, logo_url, meta_title, meta_description, facebook_url, instagram_url, twitter_url, youtube_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $settings = ['site_name' => $site_name, 'tagline' => $tagline, 'header_text' => $header_text, 'header_cta_text' => $header_cta_text, 'header_cta_link' => $header_cta_link, 'footer_about' => $footer_about, 'footer_email' => $footer_email, 'footer_phone' => $footer_phone, 'footer_address' => $footer_address, 'footer_cta_heading' => $footer_cta_heading, 'footer_cta_text' => $footer_cta_text, 'footer_cta_button_text' => $footer_cta_button_text, 'footer_cta_button_link' => $footer_cta_button_link, 'footer_copyright' => $footer_copyright, 'favicon_url' => $favicon_url, 'logo_url' => $logo_url, 'meta_title' => $meta_title, 'meta_description' => $meta_description, 'facebook_url' => $facebook_url, 'instagram_url' => $instagram_url, 'twitter_url' => $twitter_url, 'youtube_url' => $youtube_url];
    $success = 'Settings updated successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Site Settings</title>
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
            <h3 class="fw-bold mb-1">Header & Footer Content</h3>
            <p class="text-muted mb-0">Update the branding, hero call-to-action and footer details.</p>
          </div>
          <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
        <div class="card admin-card">
          <div class="card-body">
            <?php if (!empty($success)): ?>
              <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
              <div class="mb-3">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Sagar Art'); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Tagline</label>
                <input type="text" name="tagline" class="form-control" value="<?php echo htmlspecialchars($settings['tagline'] ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Header Text</label>
                <textarea name="header_text" class="form-control" rows="3"><?php echo htmlspecialchars($settings['header_text'] ?? ''); ?></textarea>
              </div>
              <div class="row g-3">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Header CTA Text</label>
                  <input type="text" name="header_cta_text" class="form-control" value="<?php echo htmlspecialchars($settings['header_cta_text'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Header CTA Link</label>
                  <input type="text" name="header_cta_link" class="form-control" value="<?php echo htmlspecialchars($settings['header_cta_link'] ?? ''); ?>">
                </div>
              </div>
              <div class="row g-3">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Favicon</label>
                  <input type="file" name="favicon" class="form-control">
                  <?php if (!empty($settings['favicon_url'])): ?><small class="text-muted d-block mt-2">Current: <a href="../<?php echo htmlspecialchars($settings['favicon_url']); ?>" target="_blank">View file</a></small><?php endif; ?>
                  <input type="hidden" name="existing_favicon_url" value="<?php echo htmlspecialchars($settings['favicon_url'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Logo</label>
                  <input type="file" name="logo" class="form-control">
                  <?php if (!empty($settings['logo_url'])): ?><small class="text-muted d-block mt-2">Current: <a href="../<?php echo htmlspecialchars($settings['logo_url']); ?>" target="_blank">View file</a></small><?php endif; ?>
                  <input type="hidden" name="existing_logo_url" value="<?php echo htmlspecialchars($settings['logo_url'] ?? ''); ?>">
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Meta Title</label>
                <input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($settings['meta_title'] ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Meta Description</label>
                <textarea name="meta_description" class="form-control" rows="3"><?php echo htmlspecialchars($settings['meta_description'] ?? ''); ?></textarea>
              </div>
              <div class="row g-3">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Facebook URL</label>
                  <input type="url" name="facebook_url" class="form-control" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Instagram URL</label>
                  <input type="url" name="instagram_url" class="form-control" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Twitter URL</label>
                  <input type="url" name="twitter_url" class="form-control" value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">YouTube URL</label>
                  <input type="url" name="youtube_url" class="form-control" value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>">
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Footer About</label>
                <textarea name="footer_about" class="form-control" rows="3"><?php echo htmlspecialchars($settings['footer_about'] ?? ''); ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Footer CTA Heading</label>
                <input type="text" name="footer_cta_heading" class="form-control" value="<?php echo htmlspecialchars($settings['footer_cta_heading'] ?? 'Build memorable digital experiences.'); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Footer CTA Text</label>
                <textarea name="footer_cta_text" class="form-control" rows="3"><?php echo htmlspecialchars($settings['footer_cta_text'] ?? 'Create more persuasive pages, polished service showcases, and faster contact flows with a website designed for conversions.'); ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Footer CTA Button Text</label>
                <input type="text" name="footer_cta_button_text" class="form-control" value="<?php echo htmlspecialchars($settings['footer_cta_button_text'] ?? 'Get in touch'); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Footer CTA Button Link</label>
                <input type="text" name="footer_cta_button_link" class="form-control" value="<?php echo htmlspecialchars($settings['footer_cta_button_link'] ?? 'contact.php'); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Footer Email</label>
                <input type="email" name="footer_email" class="form-control" value="<?php echo htmlspecialchars($settings['footer_email'] ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Footer Phone</label>
                <input type="text" name="footer_phone" class="form-control" value="<?php echo htmlspecialchars($settings['footer_phone'] ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Footer Address</label>
                <textarea name="footer_address" class="form-control" rows="2"><?php echo htmlspecialchars($settings['footer_address'] ?? ''); ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Footer Copyright</label>
                <input type="text" name="footer_copyright" class="form-control" value="<?php echo htmlspecialchars($settings['footer_copyright'] ?? ''); ?>">
              </div>
              <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
<?php $conn->close(); ?>
