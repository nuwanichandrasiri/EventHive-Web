<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EventHive — University Event Registration</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<nav>
  <div class="brand">Event<span>Hive</span></div>
  <div class="nav-links">
    <a href="index.php" class="active">Home</a>
    <a href="student.php">Student Panel</a>
    <a href="admin.php">Admin Panel</a>
  </div>
</nav>

<?php require 'db.php';
$total_events = $conn->query("SELECT COUNT(*) as c FROM events")->fetch_assoc()['c'];
$total_students = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$total_regs = $conn->query("SELECT COUNT(*) as c FROM registrations")->fetch_assoc()['c'];
?>

<!-- Hero -->
<div class="hero">
  <div class="hero-bg"></div>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <p class="hero-label">KIU Sri Lanka — University Events</p>
    <h1 class="hero-title">
      Event
      <span>Hive</span>
    </h1>
    <p class="hero-subtitle">Your central hub for discovering and registering for university events — physical or online.</p>
    <div class="hero-stats">
      <div class="hero-stat">
        <h4><?= $total_events ?></h4>
        <p>Events</p>
      </div>
      <div class="hero-stat">
        <h4><?= $total_students ?></h4>
        <p>Students</p>
      </div>
      <div class="hero-stat">
        <h4><?= $total_regs ?></h4>
        <p>Registrations</p>
      </div>
    </div>
    <div class="btn-group">
      <a href="student.php" class="btn btn-primary">Register Now</a>
      <a href="admin.php" class="btn btn-outline">Admin Panel</a>
    </div>
  </div>
</div>

<!-- Ticker -->
<div class="ticker">
  <div class="ticker-inner">
    <span class="ticker-item">UPCOMING EVENTS</span>
    <span class="ticker-item">REGISTER NOW</span>
    <span class="ticker-item">PHYSICAL & ONLINE</span>
    <span class="ticker-item">KIU SRI LANKA</span>
    <span class="ticker-item">EVENTHIVE</span>
    <span class="ticker-item">UPCOMING EVENTS</span>
    <span class="ticker-item">REGISTER NOW</span>
    <span class="ticker-item">PHYSICAL & ONLINE</span>
    <span class="ticker-item">KIU SRI LANKA</span>
    <span class="ticker-item">EVENTHIVE</span>
  </div>
</div>

<!-- Events -->
<div class="container">
  <div class="section-header">
    <p class="section-label">What's Going On</p>
    <h2 class="section-title">UPCOMING EVENTS</h2>
    <p class="section-subtitle">Browse and register for events happening at your university.</p>
  </div>

  <div class="events-grid">
    <?php
    $result = $conn->query("SELECT * FROM events WHERE status != 'Completed' ORDER BY date ASC");
    if ($result->num_rows > 0):
      while ($event = $result->fetch_assoc()):
        $percent = $event['capacity'] > 0 ? ($event['registered_count'] / $event['capacity']) * 100 : 0;
    ?>
      <div class="event-card">
        <div class="event-card-image"></div>
        <div class="event-card-body">
          <h3><?= htmlspecialchars($event['name']) ?></h3>
          <p>📅 <?= $event['date'] ?> &nbsp;|&nbsp; <?= $event['type'] ?></p>
          <p>📍 <?= htmlspecialchars($event['location']) ?></p>
          <p><?= htmlspecialchars($event['description']) ?></p>
          <div class="slots-bar">
            <div class="slots-fill" style="width:<?= $percent ?>%"></div>
          </div>
        </div>
        <div class="event-card-footer">
          <span class="slots-text-sm"><?= $event['capacity'] - $event['registered_count'] ?> slots left</span>
          <a href="student.php" class="btn btn-outline btn-sm">Register</a>
        </div>
      </div>
    <?php endwhile; else: ?>
      <div class="empty">No upcoming events at the moment.</div>
    <?php endif; ?>
  </div>
</div>

<footer>
  <div class="footer-brand">Event<span>Hive</span></div>
  <p>University Event Registration System &copy; <?= date('Y') ?></p>
</footer>

</body>
</html>