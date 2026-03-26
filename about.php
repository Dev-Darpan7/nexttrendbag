<?php
session_start();
require_once 'db.php';
$depth = 0;
$pageTitle = 'About Us – NextTrendBag';
$cartCount = getCartCount($pdo); $wishlistCount = getWishlistCount($pdo); $currentPage = 'about';
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
<div class="page-banner">
  <span class="section-label">Our Story</span>
  <h1>Crafting Excellence Since 2018</h1>
  <p>Where passion for design meets the perfection of craft</p>
</div>

<!-- Mission -->
<section class="section-pad bg-white">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;">
      <div>
        <span class="section-label">Who We Are</span>
        <h2 style="margin:10px 0 20px;">Born from a Love of <span style="color:var(--brown);">Beautiful Bags</span></h2>
        <p style="font-size:16px;color:var(--gray-700);line-height:1.8;margin-bottom:16px;">NextTrendBag was born from a simple idea — create bags that combine timeless style with modern functionality. We believe your bag is more than an accessory; it's a companion for life's journeys.</p>
        <p style="font-size:16px;color:var(--gray-700);line-height:1.8;">Our team of skilled artisans handcrafts every bag using premium, sustainably sourced materials. From the first sketch to the final stitch, quality is our obsession.</p>
        <div style="display:flex;gap:32px;margin-top:32px;">
          <div style="text-align:center;">
            <div style="font-family:'Playfair Display',serif;font-size:2.5rem;font-weight:700;color:var(--brown);">50K+</div>
            <div style="font-size:13px;color:var(--gray-500);">Happy Customers</div>
          </div>
          <div style="text-align:center;">
            <div style="font-family:'Playfair Display',serif;font-size:2.5rem;font-weight:700;color:var(--brown);">200+</div>
            <div style="font-size:13px;color:var(--gray-500);">Products</div>
          </div>
          <div style="text-align:center;">
            <div style="font-family:'Playfair Display',serif;font-size:2.5rem;font-weight:700;color:var(--brown);">7+</div>
            <div style="font-size:13px;color:var(--gray-500);">Years Experience</div>
          </div>
        </div>
      </div>
      <div style="border-radius:var(--radius-lg);overflow:hidden;aspect-ratio:4/3;">
        <img src="images/Screenshot 2026-02-24 093022.png" alt="Our Craft" style="width:100%;height:100%;object-fit:cover;">
      </div>
    </div>
  </div>
</section>

<!-- Values -->
<section class="section-pad bg-beige">
  <div class="container">
    <div class="section-head"><span class="section-label">Our Values</span><h2>What Drives Us</h2></div>
    <div class="grid-3">
      <?php $values = [
        ['🎨','Craftsmanship','Each bag is meticulously crafted with time-honored techniques and modern precision.'],
        ['🌿','Sustainability','We\'re committed to eco-friendly practices and responsible sourcing.'],
        ['❤️','Community','Our 50,000+ customers are the heart of everything we do.'],
      ]; foreach ($values as [$icon,$title,$text]): ?>
      <div class="feature-card" style="text-align:center;">
        <div style="font-size:3rem;margin-bottom:16px;"><?= $icon ?></div>
        <h3 style="margin-bottom:10px;"><?= $title ?></h3>
        <p style="color:var(--gray-500);font-size:14px;margin:0;"><?= $text ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section-pad bg-white" style="text-align:center;">
  <div class="container" style="max-width:620px;">
    <h2 style="margin-bottom:16px;">Ready to Find Your Perfect Bag?</h2>
    <p style="color:var(--gray-500);margin-bottom:32px;">Browse our curated collections and discover a bag that's made for you.</p>
    <a href="shop.php" class="btn btn-primary" style="font-size:16px;">Shop Collection</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="bag.js"></script>
</body>
</html>
