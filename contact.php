<?php
session_start();
require_once 'db.php';
$depth = 0;
$success = $error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = filter_var($_POST['email']??'', FILTER_SANITIZE_EMAIL);
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$name || !$email || !$message) { $error = 'Please fill all required fields.'; }
    else { $success = "Thank you, $name! We'll get back to you within 24 hours."; }
}
$pageTitle = 'Contact Us – NextTrendBag';
$cartCount = getCartCount($pdo); $wishlistCount = getWishlistCount($pdo); $currentPage = 'contact';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= $pageTitle ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="bag.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="page-banner"><span class="section-label">Get in Touch</span><h1>Contact Us</h1><p>We'd love to hear from you. Send us a message and we'll respond within 24 hours.</p></div>
<section class="section-pad bg-white">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;">
      <!-- Form -->
      <div>
        <h2 style="margin-bottom:24px;">Send a Message</h2>
        <?php if ($success): ?><div class="flash flash-success"><?= $success ?></div>
        <?php elseif ($error): ?><div class="flash flash-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if (!$success): ?>
        <form method="POST">
          <div class="form-grid-2">
            <div class="form-group"><label>Name *</label><input type="text" name="name" required placeholder="Your name" value="<?= htmlspecialchars($_POST['name']??'') ?>"></div>
            <div class="form-group"><label>Email *</label><input type="email" name="email" required placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email']??'') ?>"></div>
          </div>
          <div class="form-group"><label>Subject</label><input type="text" name="subject" placeholder="What's this about?" value="<?= htmlspecialchars($_POST['subject']??'') ?>"></div>
          <div class="form-group"><label>Message *</label><textarea name="message" rows="6" required placeholder="Your message..." style="font-family:inherit;resize:vertical;"><?= htmlspecialchars($_POST['message']??'') ?></textarea></div>
          <button type="submit" class="btn btn-primary">Send Message →</button>
        </form>
        <?php endif; ?>
      </div>
      <!-- Info -->
      <div style="display:flex;flex-direction:column;gap:20px;">
        <h2 style="margin-bottom:4px;">We're Here for You</h2>
        <?php $infos = [
          ['📍','Our Address','123 Fashion Street, Koramangala<br>Bangalore, Karnataka 560034'],
          ['✉️','Email Us','<a href="mailto:hello@nexttrendbag.com" style="color:var(--brown);">hello@nexttrendbag.com</a><br><a href="mailto:support@nexttrendbag.com" style="color:var(--brown);">support@nexttrendbag.com</a>'],
          ['📞','Call Us','+91 98765 43210<br><small style="color:var(--gray-500);">Mon-Sat: 9am – 6pm IST</small>'],
          ['⏰','Business Hours','Mon – Sat: 9:00 AM – 6:00 PM IST<br>Sunday: Closed'],
        ];
        foreach ($infos as [$icon,$label,$value]): ?>
        <div style="display:flex;gap:18px;align-items:flex-start;background:var(--beige-50);border-radius:var(--radius);padding:20px;">
          <div style="font-size:1.8rem;flex-shrink:0;"><?= $icon ?></div>
          <div>
            <h4 style="margin-bottom:6px;"><?= $label ?></h4>
            <p style="margin:0;font-size:14px;color:var(--gray-700);line-height:1.7;"><?= $value ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
