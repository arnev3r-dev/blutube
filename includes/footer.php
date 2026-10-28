<?php
declare(strict_types=1);
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$inAdmin = (bool)preg_match('#/admin$#', $scriptDir);
$ROOT = $inAdmin ? '..' : '.';
?>
  </main>
</div>
<script src="<?= htmlspecialchars($ROOT, ENT_QUOTES, 'UTF-8') ?>/assets/app.js"></script>
</body>
</html>
