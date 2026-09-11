@php
$title = 'Events & Schedule';
$description = 'Official Mass schedules, liturgical devotions, and community celebrations of Our Lady of the Pillar Parish.';
$columns = ['Liturgical Celebration / Event','Frequency / Date','Time','Location'];
$rows = [
    ['Sunday Holy Mass (Early Morning)', 'Every Sunday', '5:00 AM', 'Shrine Main Altar'],
    ['Sunday Holy Mass (Morning with FB Live)', 'Every Sunday', '7:30 AM', 'Shrine Main Altar (Livestreamed)'],
    ['Sunday Holy Mass (Afternoon with FB Live)', 'Every Sunday', '5:00 PM', 'Shrine Main Altar (Livestreamed)'],
    ['Daily Mass (Mon & Wed)', 'Every Monday & Wednesday', '5:00 PM', 'Shrine Main Altar'],
    ['Daily Mass (Tue, Thu, Fri, Sat)', 'Tuesday, Thursday & Friday, Saturday', '6:00 AM', 'Shrine Main Altar'],
    ['Anticipated Sunday Mass', 'Every Saturday', '5:00 PM', 'Shrine Main Altar'],
    ['Sacrament of Reconciliation (Confession)', 'Every 1st Thursday of the Month', '5:00 PM', 'Shrine Confessional'],
    ['Devotion to Our Lady of the Pillar & Marian Procession', 'Every 12th of the Month', '5:00 PM Mass / 6:00 PM Procession', 'Shrine & Town Procession Route'],
    ['Misa sa Campo Santo', 'Every 1st Monday of the Month', '6:00 AM', 'Campo Santo Chapel'],
    ['Healing Mass', 'Every 1st Tuesday of the Month', '6:00 AM', 'Shrine Main Altar'],
    ['Holy Mass at Banuyo', 'Every 1st Saturday of the Month', '6:00 AM', 'Our Lady of Fatima Chapel (Banuyo)'],
    ['First Friday Holy Hour', 'Every 1st Friday of the Month', 'After Morning Mass', 'Shrine Main Altar'],
    ['Parish Patronal Fiesta of Our Lady of the Pillar', 'October 12 (Annual)', 'Full Day Observance', 'Diocesan Shrine Grounds']
];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))