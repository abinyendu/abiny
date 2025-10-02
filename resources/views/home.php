<section class="hero">
  <div class="hero-inner">
    <div class="hero-text">
      <h1>Discover Premium Ethiopian Products</h1>
      <p>Coffee, spices, honey, textiles, and more — locally and globally shipped.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="/c/coffee">Shop Coffee</a>
        <a class="btn btn-secondary" href="/categories">Browse Categories</a>
      </div>
    </div>
  </div>
</section>

<section class="categories-strip">
  <h2>Top Categories</h2>
  <div class="carousel">
    <a class="card" href="/c/coffee">Coffee</a>
    <a class="card" href="/c/spices">Spices</a>
    <a class="card" href="/c/honey">Honey</a>
    <a class="card" href="/c/textiles">Textiles</a>
    <a class="card" href="/c/jewelry">Jewelry</a>
    <a class="card" href="/c/organic-foods">Organic Foods</a>
  </div>
</section>

<section>
  <h2>Featured Products</h2>
  <div class="grid products">
    <?php foreach (($featured ?? []) as $p): ?>
      <div class="product-card">
        <div class="image">
          <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
          <button class="quick-view" data-product="<?php echo htmlspecialchars($p['title']); ?>">Quick View</button>
        </div>
        <div class="details">
          <div class="title"><?php echo htmlspecialchars($p['title']); ?></div>
          <div class="price">$<?php echo number_format($p['price'], 2); ?></div>
          <div class="actions">
            <button class="btn btn-primary">Add to Cart</button>
            <button class="btn btn-ghost">Wishlist</button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="testimonials">
  <h2>What buyers say</h2>
  <div class="grid">
    <blockquote>“Outstanding coffee quality and fast shipping.” — Sara</blockquote>
    <blockquote>“Authentic spices with rich aroma.” — Daniel</blockquote>
    <blockquote>“Great marketplace for Ethiopian artisans.” — Hana</blockquote>
  </div>
</section>

<section class="newsletter">
  <h2>Join Our Newsletter</h2>
  <form action="/newsletter" method="post" class="newsletter-form">
    <input type="email" name="email" placeholder="Email address">
    <button class="btn btn-primary">Subscribe</button>
  </form>
</section>
