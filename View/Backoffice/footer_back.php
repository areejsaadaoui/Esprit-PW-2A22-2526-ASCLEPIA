<footer class="footer" style="margin-top: auto; border-top: 1px solid var(--border);">
  <div class="container" style="max-width: 100%;">
    <div class="row" style="gap: 48px;">

      <!-- Brand -->
      <div style="flex: 0 0 260px;">
        <div class="footer-brand">
          <div class="navbar-brand" style="margin-bottom: 16px; display: flex; align-items: center;">
            <img src="../assets/image/logo.png?v=<?php echo time(); ?>" alt="ASCLEPIA Logo" style="height: 65px; object-fit: contain;">
          </div>
          <p>Votre plateforme médicale complète. Gestion BackOffice des pharmacies.</p>
          <div class="social-links">
            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>
      </div>

      <!-- Liens -->
      <div class="col">
        <div class="footer-section">
          <h4>Liens Backoffice</h4>
          <ul class="footer-links" style="list-style: none; padding: 0;">
            <li><a href="listepharmacie.php" style="color: var(--text-muted); text-decoration: none;"><i class="fa-solid fa-list"></i> Liste Pharmacies</a></li>
            <li><a href="addpharmacie.php" style="color: var(--text-muted); text-decoration: none;"><i class="fa-solid fa-plus"></i> Ajouter Pharmacie</a></li>
            <li><a href="../frontoffice/index.html" style="color: var(--text-muted); text-decoration: none;"><i class="fa-solid fa-home"></i> Retour Site</a></li>
          </ul>
        </div>
      </div>

    </div>

    <div class="footer-bottom" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border); text-align: center;">
      <p style="margin: 0;">© 2026 <a href="../frontoffice/index.html">ASCLEPIA</a>. Tous droits réservés.</p>
    </div>
  </div>
</footer>

</div> <!-- End of main-content -->

<script src="script.js"></script>
<script>
  function toggleMenu() {
    // Menu functionality if needed for mobile sidebar
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
  }
</script>

</body>
</html>
