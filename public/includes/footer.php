    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
    (function() {
      var key = 'bl_theme';
      var html = document.documentElement;
      var body = document.body;

      function apply(theme) {
        if (html) html.setAttribute('data-bs-theme', theme);
        if (body) body.setAttribute('data-bs-theme', theme);
        try { localStorage.setItem(key, theme); } catch(e) {}
        // Update icon to show the *action*
        var btn = document.getElementById('themeToggle');
        if (btn) btn.textContent = (theme === 'dark') ? '☀️' : '🌙';
      }

      function initial() {
        try {
          var saved = localStorage.getItem(key);
          if (saved === 'light' || saved === 'dark') return saved;
        } catch(e) {}
        // Autodetect if no saved setting
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark';
        return 'light';
      }

      window.toggleTheme = function toggleTheme() {
        var current = (html && html.getAttribute('data-bs-theme')) || 'light';
        var next = (current === 'dark') ? 'light' : 'dark';
        apply(next);
      };

      function init() {
        apply(initial());
        // if no saved preference, follow OS changes until user toggles
        try {
          if (!localStorage.getItem(key) && window.matchMedia) {
            var mq = window.matchMedia('(prefers-color-scheme: dark)');
            if (mq.addEventListener) mq.addEventListener('change', function(e){ apply(e.matches ? 'dark' : 'light'); });
            else if (mq.addListener) mq.addListener(function(e){ apply(e.matches ? 'dark' : 'light'); });
          }
        } catch(e) {}
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
      } else {
        init();
      }
    })();
    </script>
  </body>
</html>
