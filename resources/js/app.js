import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// === Setup Highcharts ===
import Highcharts from 'highcharts';

import Data from 'highcharts/modules/data';
import Drilldown from 'highcharts/modules/drilldown';
import Exporting from 'highcharts/modules/exporting';
import ExportData from 'highcharts/modules/export-data';
import Accessibility from 'highcharts/modules/accessibility';

// Fungsi helper agar Vite tidak bingung membaca modulnya
const initModule = (module) => {
    if (typeof module === 'function') {
        module(Highcharts);
    } else if (module && typeof module.default === 'function') {
        module.default(Highcharts);
    }
};

// Inisialisasi modul dengan fungsi helper
initModule(Data);
initModule(Drilldown);
initModule(Exporting);
initModule(ExportData);
initModule(Accessibility);

// Jadikan global
window.Highcharts = Highcharts;