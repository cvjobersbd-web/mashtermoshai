<?php $ViewData['Title'] = 'Error'; ?>
<h1 class="text-danger">Error.</h1>
<h2 class="text-danger">An error occurred while processing your request.</h2>
<?php if (!empty($Model->Message)): ?><p><?= e($Model->Message) ?></p><?php endif; ?>
<?php if (!empty($Model->RequestId)): ?><p><strong>Request ID:</strong> <code><?= e($Model->RequestId) ?></code></p><?php endif; ?>
<p><a href="/">Go to Home</a></p>
