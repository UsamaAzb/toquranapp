@php
  use Illuminate\Support\Facades\Vite;
  use App\Helpers\Helpers;

  // Get primary color - first from cookie, then from config
  $brandPrimaryColor = Helpers::normalizePrimaryColor(config('custom.custom.primaryColor'));
  $cookiePrimaryColor = isset($_COOKIE['front-primaryColor']) ? Helpers::normalizePrimaryColor($_COOKIE['front-primaryColor']) : null;
  $primaryColor =
      $cookiePrimaryColor && ! Helpers::isLegacyPrimaryColor($cookiePrimaryColor, $brandPrimaryColor)
          ? $cookiePrimaryColor
          : $configData['color'] ?? null;
  $templateCustomizerColors = [
      ['name' => 'brand', 'title' => 'To Quran Gold', 'color' => $brandPrimaryColor ?? '#c9a24d'],
      ['name' => 'success', 'title' => 'Success', 'color' => '#0D9394'],
      ['name' => 'warning', 'title' => 'Warning', 'color' => '#FFAB1D'],
      ['name' => 'danger', 'title' => 'Danger', 'color' => '#EB3D63'],
      ['name' => 'info', 'title' => 'Info', 'color' => '#2092EC'],
  ];
@endphp
<!-- laravel style -->
@vite(['resources/assets/vendor/js/helpers.js'])
<!-- beautify ignore:start -->
@if ($configData['hasCustomizer'] && $configData['displayCustomizer'])
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  @vite(['resources/assets/vendor/js/template-customizer.js'])
@endif

  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
@vite(['resources/assets/js/front-config.js'])

@if ($configData['hasCustomizer'] && $configData['displayCustomizer'])
<script type="module">
  document.addEventListener('DOMContentLoaded', function() {
    const legacyPrimaryColors = ['#2092ec', '#696cff', '#7367f0', '#0d6efd'];
    const normalizeColor = color => String(color || '').trim().toLowerCase();
    const layoutName = document.documentElement.getAttribute('data-template') || window.templateName || 'front-menu-template';
    const localStorageColorKey = `templateCustomizer-${layoutName}--Color`;
    const localStorageMigrationKey = `templateCustomizer-${layoutName}--ToQuranPrimaryColorMigration`;

    try {
      if (localStorage.getItem(localStorageMigrationKey) !== 'true') {
        const savedColor = normalizeColor(localStorage.getItem(localStorageColorKey));

        if (!savedColor || legacyPrimaryColors.includes(savedColor)) {
          localStorage.removeItem(localStorageColorKey);
        }

        localStorage.setItem(localStorageMigrationKey, 'true');
      }
    } catch (error) {
      console.warn('Template primary color migration skipped:', error);
    }

    // Initialize template customizer after DOM is loaded
    if (window.TemplateCustomizer) {
      try {
        window.templateCustomizer = new TemplateCustomizer({
          defaultTextDir: "{{ $configData['textDirection'] }}",
          @if ($primaryColor)
            defaultPrimaryColor: "{{ $primaryColor }}",
          @endif
          defaultTheme: "{{ $configData['themeOpt'] }}",
          defaultShowDropdownOnHover: "{{ $configData['showDropdownOnHover'] }}",
          displayCustomizer: "{{ $configData['displayCustomizer'] }}",
          lang: '{{ app()->getLocale() }}',
          availableColors: @json($templateCustomizerColors),
          'controls': @json(['color', 'theme', 'rtl']),
        });

        // Ensure color is applied on page load
        @if ($primaryColor)
          if (window.Helpers && typeof window.Helpers.setColor === 'function') {
            window.Helpers.setColor("{{ $primaryColor }}", true);
          }
        @endif
      } catch (error) {
        console.warn('Template customizer initialization error:', error);
      }
    }
  });
</script>
@endif
