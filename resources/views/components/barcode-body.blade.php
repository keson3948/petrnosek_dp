<body class="{{ $attributes->get('class', 'min-h-screen font-sans antialiased bg-base-200') }}"
      x-data="{
          barcode: '',
          lastTime: 0,
          handleKeydown(e) {
              const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
              if (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select') {
                  return;
              }

              const currentTime = new Date().getTime();

              if (currentTime - this.lastTime > 50) {
                  this.barcode = '';
              }

              this.lastTime = currentTime;

              if (e.key === 'Enter' && this.barcode.length > 0) {
                  Livewire.dispatch('qr-scanned', { code: this.barcode });
                  this.barcode = '';
                  return;
              }

              if (e.key.length === 1 && !e.ctrlKey && !e.metaKey) {
                  this.barcode += e.key;
              }
          }
      }"
      @keydown.window="handleKeydown"
>
{{ $slot }}
</body>
