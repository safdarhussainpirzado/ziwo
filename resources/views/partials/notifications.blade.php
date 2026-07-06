<script>
    (function() {
        // This script will run every time this partial is loaded (on page load or Bento Bridge swap)
        const triggerNotifications = () => {
            @if (session('success')) showSuccess("{{ session('success') }}"); @endif
            @if (session('error')) showError("{{ session('error') }}"); @endif
            @if (session('failure')) showError("{{ session('failure') }}", "Failure"); @endif
            @if (session('warning')) showWarning("{{ session('warning') }}"); @endif
            @if (session('info')) showInfo("{{ session('info') }}"); @endif
            
            @if ($errors->any())
                showError({!! json_encode($errors->all()) !!}, "Policy Violations");
            @endif
        };

        // If notification engine is ready, fire immediately
        if (window.showSuccess) {
            triggerNotifications();
        } else {
            // Otherwise wait for it
            document.addEventListener('DOMContentLoaded', triggerNotifications);
        }
    })();
</script>
