import './bootstrap';
import './softphone/index.js';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';

Alpine.plugin(collapse);

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();
// cache-buster: 1784734399