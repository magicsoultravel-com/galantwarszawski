</main>
<footer>

<p>
<?= spawn_content('short', ['show_title' => false]) ?><br>
<p>&copy; <?= date('Y') ?> galantwarszawski.pl</p>
        


     <div class="bottom-login-status">
        <?php if ($isLoggedIn): ?>
            <?php if (is_admin()): ?>
                <nav class="admin-nav">
                    <?php
                    $adminPanelLink = dirname($_SERVER['SCRIPT_NAME']) . '../admin/panel.php';
                    ?>
                    <a href="<?= htmlspecialchars($adminPanelLink) ?>">Admin Panel</a>
                </nav>
            <?php endif; ?>

            <div class="user-status">
                Status: Logged in as <?= htmlspecialchars($email) ?> |
                <?php
                $logoutLink = dirname($_SERVER['SCRIPT_NAME']) . '../templates/auth/logout.php';
                ?>
                <a href="<?= htmlspecialchars($logoutLink) ?>">Logout</a>
            </div>
        <?php else: ?>
            <?php
            $formActionPath = dirname($_SERVER['SCRIPT_NAME']) . '../templates/auth/login.php';
            ?>
            <form class="login-form" method="post" action="<?= htmlspecialchars($formActionPath) ?>" autocomplete="off">
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <br><button type="submit">Login</button>
            </form>
            <div class="user-status">
                Status: Logged out
            </div>
        <?php endif; ?>
    </div>
</footer>
</body>
</html>
