export const defaultCategories = [
  {
    title: 'Daily Mass',
    tag: 'Monday to Saturday',
    icon: '◷',
    items: [
      { id: 4, day: 'Monday & Wednesday', time: '5:00 PM — Holy Mass', highlighted: false, live: false },
      { id: 5, day: 'Tuesday, Thursday & Friday', time: '6:00 AM — Holy Mass', highlighted: false, live: false },
      { id: 6, day: 'Saturday', time: '6:00 AM — Holy Mass', highlighted: false, live: false },
      { id: 7, day: 'Anticipated Mass (Saturday)', time: '5:00 PM — Anticipated Sunday Mass', highlighted: true, live: false },
    ],
  },
  {
    title: 'Sunday Mass',
    tag: "Lord's Day Celebrations",
    icon: '✝',
    items: [
      { id: 8, day: 'Early Morning', time: '5:00 AM — Holy Mass', highlighted: false, live: false },
      { id: 1, day: 'Morning (Live)', time: '7:30 AM — Holy Mass', highlighted: false, live: true },
      { id: 9, day: 'Afternoon (Live)', time: '5:00 PM — Holy Mass', highlighted: false, live: true },
    ],
  },
  {
    title: 'Sacrament of Reconciliation',
    tag: 'Confession & Spiritual Healing',
    icon: '✦',
    items: [
      { id: 10, day: 'Every First Thursday of the Month', time: '5:00 PM — Confession', highlighted: false, live: false },
    ],
  },
  {
    title: 'Monthly Devotion to Our Lady of the Pillar',
    tag: 'Patronal Devotional Day',
    icon: '♛',
    items: [
      { id: 11, day: 'Every 12th of the Month', time: '5:00 PM — Holy Mass', highlighted: false, live: false },
      { id: 12, day: 'Procession', time: '6:00 PM — Marian Procession', highlighted: false, live: false },
    ],
  },
  {
    title: 'Special Liturgical Activities',
    tag: 'Monthly Observances & Chapels',
    icon: '▦',
    items: [
      { id: 13, day: 'Every First Tuesday', time: '6:00 AM — Healing Mass', highlighted: false, live: false },
      { id: 14, day: 'Every First Monday', time: '6:00 AM — Misa sa Campo Santo', highlighted: false, live: false },
      { id: 15, day: 'First Saturday', time: '6:00 AM — Mass at Our Lady of Fatima Chapel (Banuyo)', highlighted: false, live: false },
      { id: 16, day: 'Every First Friday', time: 'Holy Hour after Holy Mass', highlighted: false, live: false },
    ],
  },
]
