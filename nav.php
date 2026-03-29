<?php
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>

<!-- Custom Navbar Styling -->
<style>
  .navbar-custom {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 75px;
    background-color: #222;
    border: none;
    border-radius: 0;
    z-index: 9999;
    width: 100%;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    padding: 0 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  body {
    padding-top: 85px; /* Prevent content from being hidden under fixed navbar */
  }

  .navbar-brand {
    display: flex;
    align-items: center;
    color: #fff !important;
    text-decoration: none;
  }

  .navbar-brand img {
    height: 50px;
    margin-right: 12px;
  }

  .navbar-brand span {
    font-size: 20px;
    font-weight: 500;
    color: #fff;
  }

  .navbar-nav {
    display: flex;
    list-style: none;
    margin: 0;
    padding-left: 0;
  }

  .navbar-nav li {
    margin-left: 20px;
  }

  .navbar-nav a {
    color: #ddd;
    text-decoration: none;
    font-size: 16px;
    padding: 15px 10px;
    display: block;
  }

  .navbar-nav a:hover {
    color: #fff;
  }

  .navbar-right {
    margin-left: auto;
  }

  @media (max-width: 768px) {
    .navbar-brand span {
      font-size: 16px;
    }
    .navbar-nav a {
      font-size: 15px;
      padding: 10px 5px;
    }
    .navbar-custom {
      padding: 0 15px;
    }
  }
</style>

<script>
  (function() {
    var setFavicon = function() {
      try {
        var head = document.head || document.getElementsByTagName('head')[0];
        if (!head) return;
        var existing = head.querySelector('link[rel="icon"], link[rel="shortcut icon"]');
        var href = 'images/MISA-2025.png';
        if (existing) {
          existing.href = href;
        } else {
          var link = document.createElement('link');
          link.rel = 'icon';
          link.type = 'image/png';
          link.href = href;
          head.appendChild(link);
        }
      } catch (e) { /* no-op */ }
    };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', setFavicon);
    } else {
      setFavicon();
    }
  })();
  </script>

<nav class="navbar-custom">
  <!-- Brand (Logo and Title) -->
  <a class="navbar-brand" href="landing.php">
    <img src="images/SA_Logo-removebg.png" alt="Logo">
    <span>SPECANCIENS - IMS</span>
  </a>

  <!-- Navigation Links -->
  <ul class="navbar-nav">
    <?php if ($role === 'admin' || $role === 'interviewer'): ?>
      <li><a href="viewCandidate.php">View Candidates</a></li>
      <li><a href="viewQuestions.php">View Questions</a></li>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
      <li><a href="addCandidate.php">Add Candidate</a></li>
      <li><a href="addPerformance.php">Add Performance</a></li>
      <li><a href="admin_psychometric_scores.php">View psychometric score</a></li>

      <?php if ($role === 'admin'): ?>
        <li><a href="manage_questions.php">Coding Questions</a></li>
        <li><a href="add_question.php">Add Question</a></li>
      <?php endif; ?>
      
    <?php endif; ?>

    <?php if ($role === 'student'): ?>
      <li><a href="viewMyReport.php">My Report</a></li>
      <li><a href="student_psychometric.php">Psychometric Test</a></li>
      <li><a href="coding_test.php">Technical round</a></li>
    <?php endif; ?>
  </ul>

  <!-- Logout -->
  <ul class="navbar-nav navbar-right">
    <li><a href="logout.php">Logout</a></li>
  </ul>
</nav>
