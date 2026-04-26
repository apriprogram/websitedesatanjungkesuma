<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Robustly remove any "Lihat Detail" elements from Data Wilayah card
        function cleanDataWilayahGhost() {
            console.log('Cleaning Data Wilayah ghosts...');

            // Target the Data Wilayah card specifically if possible
            const dwCard = document.getElementById('cardDataWilayah');
            const secondary = document.getElementById('cardDataWilayahSecondary');

            const targets = [dwCard, secondary, document.body];

            targets.forEach(scope => {
                if (!scope) return;

                // 1. Find buttons with "Detail" text
                const buttons = scope.querySelectorAll('button, a, div[role="button"]');
                buttons.forEach(btn => {
                    const txt = btn.innerText ? btn.innerText.trim() : '';
                    if (txt === 'Detail' || txt === 'Lihat Detail') {
                        if (!txt.includes('Lebih')) { // Don't remove "Lihat Lebih Detail" main links
                            btn.style.setProperty('display', 'none', 'important');
                            btn.remove();
                            console.log('Removed ghost button:', btn);
                        }
                    }
                });

                // 2. Find the floating white box (likely card-menu-dropdown)
                const dropdowns = scope.querySelectorAll('.card-menu-dropdown');
                dropdowns.forEach(dd => {
                    // If inside Data Wilayah, kill it
                    if (scope.id && scope.id.includes('DataWilayah')) {
                        dd.style.setProperty('display', 'none', 'important');
                        dd.remove();
                    }
                    // If it contains "Lihat Detail"
                    if (dd.innerText.includes('Lihat Detail')) {
                        dd.style.setProperty('display', 'none', 'important');
                        dd.remove();
                    }
                });

                // 3. Specific ID kill - DISABLED because it deletes the real button!
                // const mapBtn = document.getElementById('mapDetailBtn');
                // if (mapBtn) {
                //     mapBtn.style.setProperty('display', 'none', 'important');
                //     mapBtn.remove();
                // }
            });

            // 4. Global text search for "Lihat Detail" orphans (white box)
            const divs = document.querySelectorAll('div, a, button, span');
            divs.forEach(el => {
                // Check if direct text content matches "Lihat Detail" (ignoring whitespace)
                // Use includes to catch " Lihat Detail "
                const text = el.innerText ? el.innerText.trim() : '';
                
                // Specific targeting for the rogue element seen in screenshot
                if (text === 'Lihat Detail') {
                    // Check if it's NOT the main section link (which usually has "Lihat Lebih Detail" or ">")
                    if (!text.includes('Lebih') && !text.includes('>')) {
                         // Only remove small UI elements, not big containers
                        if (el.offsetHeight < 100 && el.offsetWidth < 300) {
                            el.style.setProperty('display', 'none', 'important');
                            el.remove();
                            console.log('Removed orphan Lihat Detail:', el);
                        }
                    }
                }
            });
        }

        // Run immediately and periodically
        cleanDataWilayahGhost();
        setInterval(cleanDataWilayahGhost, 1000);
    });
</script>