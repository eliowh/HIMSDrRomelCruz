<?php
    $billingName = auth()->user()->name ?? 'Billing';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Dashboard</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/billingcss/billing.css')); ?>">
</head>
<div class="sidebar" id="billing-sidebar">
    <div class="logo">
        <span class="toggle-btn" id="billingSidebarToggle">☰</span>
        <span>Billing Panel</span>
    </div>
    <nav>
        <ul>
            <li>
                <a href="<?php echo e(route('billing.dashboard')); ?>"
                   class="sidebar-btn<?php echo e(request()->routeIs('billing.dashboard', 'billing.show', 'billing.edit') ? ' active' : ''); ?>">
                    <span class="icon">📋</span> <span class="text">Patient Billing</span>
                </a>
            </li>
            <li>
                <a href="<?php echo e(route('billing.create')); ?>"
                   class="sidebar-btn<?php echo e(request()->routeIs('billing.create') ? ' active' : ''); ?>">
                    <span class="icon">➕</span> <span class="text">New Billing</span>
                </a>
            </li>
        </ul>
        <form action="/logout" method="POST" id="billing-logout-form" class="logout-form">
            <?php echo csrf_field(); ?>
            <button type="button" class="sidebar-btn" onclick="confirmLogout('billing-logout-form')">
                <span class="icon">🚪</span> <span class="text">Log Out</span>
            </button>
        </form>
    </nav>
</div>

<script>
    let isTogglingBilling = false;
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('billing-sidebar');
        const mainContent = document.querySelector('.main-content');
        const isCollapsed = localStorage.getItem('billingSidebarCollapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            if (mainContent) mainContent.classList.add('expanded');
        }

        function adjustLayoutHeight() {
            if (isTogglingBilling) return;
            if (!mainContent) return;
            requestAnimationFrame(() => {
                const contentHeight = mainContent.scrollHeight;
                const viewportHeight = window.innerHeight - 120;
                const minHeight = Math.max(contentHeight, viewportHeight);
                const currentHeight = parseInt(sidebar.style.minHeight) || 0;
                if (Math.abs(minHeight - currentHeight) > 10) {
                    sidebar.style.minHeight = minHeight + 'px';
                }
            });
        }

        let heightTimeout;
        function debouncedHeightAdjustment() {
            if (isTogglingBilling) return;
            clearTimeout(heightTimeout);
            heightTimeout = setTimeout(adjustLayoutHeight, 300);
        }

        setTimeout(() => { if (!isTogglingBilling) adjustLayoutHeight(); }, 300);
        window.addEventListener('resize', debouncedHeightAdjustment);

        const observer = new MutationObserver(function(mutations) {
            let shouldAdjust = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {
                        const hasElementChanges = Array.from(mutation.addedNodes).some(node => node.nodeType === 1) ||
                                                Array.from(mutation.removedNodes).some(node => node.nodeType === 1);
                        if (hasElementChanges) shouldAdjust = true;
                    }
                }
            });
            if (shouldAdjust) debouncedHeightAdjustment();
        });
        if (mainContent) observer.observe(mainContent, { childList: true, subtree: true });

        document.getElementById('billingSidebarToggle').addEventListener('click', function() {
            isTogglingBilling = true;
            clearTimeout(heightTimeout);
            sidebar.classList.toggle('collapsed');
            if (mainContent) mainContent.classList.toggle('expanded');
            localStorage.setItem('billingSidebarCollapsed', sidebar.classList.contains('collapsed'));
            setTimeout(() => { isTogglingBilling = false; }, 400);
        });
    });
</script>

<?php echo $__env->make('shared.logout_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\billing\billing_sidebar.blade.php ENDPATH**/ ?>