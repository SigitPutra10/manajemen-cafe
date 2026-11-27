

import Alpine from 'alpinejs';
import revenueChart from './revenue-chart';

window.Alpine = Alpine;

Alpine.data('revenueChart', revenueChart);
Alpine.start();
