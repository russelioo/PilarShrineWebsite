/**
 * Traditional Catholic English Rosary Prayers and Sacred Mysteries
 * Official liturgical & devotional texts for the Diocesan Shrine of Our Lady of the Pillar
 */

export const rosaryPrayers = {
  signOfTheCross: {
    id: 'sign-of-the-cross',
    title: 'The Sign of the Cross',
    text: `In the name of the Father, and of the Son, and of the Holy Spirit. Amen.`,
  },
  apostlesCreed: {
    id: 'apostles-creed',
    title: "The Apostles' Creed",
    latinTitle: 'Symbolum Apostolorum',
    text: `I believe in God,
the Father almighty,
Creator of heaven and earth,
and in Jesus Christ, his only Son, our Lord,
who was conceived by the Holy Spirit,
born of the Virgin Mary,
suffered under Pontius Pilate,
was crucified, died and was buried;
he descended into hell;
on the third day he rose again from the dead;
he ascended into heaven,
and is seated at the right hand of God the Father almighty;
from there he will come to judge the living and the dead.

I believe in the Holy Spirit,
the holy catholic Church,
the communion of saints,
the forgiveness of sins,
the resurrection of the body,
and life everlasting.
Amen.`,
  },
  ourFather: {
    id: 'our-father',
    title: "The Lord's Prayer (Our Father)",
    latinTitle: 'Pater Noster',
    text: `Our Father, who art in heaven,
hallowed be thy name;
thy kingdom come;
thy will be done
on earth as it is in heaven.

Give us this day our daily bread,
and forgive us our trespasses,
as we forgive those who trespass against us;

and lead us not into temptation,
but deliver us from evil.
Amen.`,
  },
  hailMary: {
    id: 'hail-mary',
    title: 'Hail Mary',
    latinTitle: 'Ave Maria',
    text: `Hail Mary, full of grace,
the Lord is with thee;
blessed art thou among women,
and blessed is the fruit of thy womb, Jesus.

Holy Mary, Mother of God,
pray for us sinners,
now and at the hour of our death.
Amen.`,
  },
  gloryBe: {
    id: 'glory-be',
    title: 'Glory Be',
    latinTitle: 'Gloria Patri',
    text: `Glory be to the Father,
and to the Son,
and to the Holy Spirit.

As it was in the beginning,
is now,
and ever shall be,
world without end.
Amen.`,
  },
  fatimaPrayer: {
    id: 'fatima-prayer',
    title: 'The Fatima Prayer',
    text: `O my Jesus,
forgive us our sins,
save us from the fire of hell;
lead all souls to Heaven,
especially those in most need of thy mercy.
Amen.`,
  },
  hailHolyQueen: {
    id: 'hail-holy-queen',
    title: 'Hail, Holy Queen',
    latinTitle: 'Salve Regina',
    text: `Hail, holy Queen, Mother of mercy,
our life, our sweetness, and our hope.
To thee do we cry, poor banished children of Eve;
to thee do we send up our sighs,
mourning and weeping in this valley of tears.

Turn then, most gracious advocate,
thine eyes of mercy toward us;
and after this our exile
show unto us the blessed fruit of thy womb, Jesus.

O clement, O loving, O sweet Virgin Mary.

Pray for us, O holy Mother of God,
that we may be made worthy of the promises of Christ.
Amen.`,
  },
  closingPrayer: {
    id: 'closing-prayer',
    title: 'Closing Rosary Prayer',
    latinTitle: 'Oremus',
    text: `O God, whose Only Begotten Son,
by his life, Death, and Resurrection,
has purchased for us the rewards of eternal life,
grant, we beseech thee,
that, meditating upon these mysteries
of the Most Holy Rosary of the Blessed Virgin Mary,
we may imitate what they contain
and obtain what they promise,
through the same Christ our Lord.
Amen.`,
  },
}

export const rosaryMysteries = {
  joyful: {
    key: 'joyful',
    title: 'The Joyful Mysteries',
    shortName: 'Joyful',
    daysLabel: 'Monday & Saturday',
    daysOfWeek: [1, 6], // 1 = Monday, 6 = Saturday
    description: 'Meditate upon the Incarnation of Jesus Christ and the joyful events of His early life with Mary.',
    colorAccent: '#c89b3c',
    items: [
      {
        number: 1,
        title: 'The Annunciation',
        meditation: 'The Angel Gabriel announces to Mary that she will conceive and bear the Son of God.',
        scripture: 'Luke 1:26–38',
        fruit: 'Humility',
      },
      {
        number: 2,
        title: 'The Visitation',
        meditation: 'Mary visits her cousin Elizabeth, who recognizes her as the Mother of the Lord.',
        scripture: 'Luke 1:39–56',
        fruit: 'Love of Neighbor & Charity',
      },
      {
        number: 3,
        title: 'The Nativity',
        meditation: 'Jesus is born in Bethlehem and laid in a manger.',
        scripture: 'Luke 2:1–20',
        fruit: 'Poverty of Spirit & Detachment',
      },
      {
        number: 4,
        title: 'The Presentation in the Temple',
        meditation: 'Mary and Joseph present Jesus in the Temple.',
        scripture: 'Luke 2:22–38',
        fruit: 'Obedience & Purity of Intention',
      },
      {
        number: 5,
        title: 'The Finding of Jesus in the Temple',
        meditation: 'Mary and Joseph find the young Jesus in the Temple among the teachers.',
        scripture: 'Luke 2:41–52',
        fruit: 'Piety & Joy in Finding God',
      },
    ],
  },
  luminous: {
    key: 'luminous',
    title: 'The Luminous Mysteries',
    shortName: 'Luminous',
    daysLabel: 'Thursday',
    daysOfWeek: [4], // 4 = Thursday
    description: 'Meditate upon the public ministry of Christ as the Light of the world.',
    colorAccent: '#1e5fad',
    items: [
      {
        number: 1,
        title: 'The Baptism of Jesus in the Jordan',
        meditation: 'Jesus is baptized in the Jordan and the Father reveals him as his beloved Son.',
        scripture: 'Matthew 3:13–17',
        fruit: 'Openness to the Holy Spirit',
      },
      {
        number: 2,
        title: 'The Wedding at Cana',
        meditation: 'At Mary’s request, Jesus performs his first public miracle, turning water into wine.',
        scripture: 'John 2:1–11',
        fruit: 'To Jesus Through Mary & Fidelity',
      },
      {
        number: 3,
        title: 'The Proclamation of the Kingdom of God',
        meditation: 'Jesus calls us to conversion and proclaims the coming of God’s Kingdom.',
        scripture: 'Mark 1:14–15',
        fruit: 'Repentance & Trust in God',
      },
      {
        number: 4,
        title: 'The Transfiguration',
        meditation: 'Jesus is transfigured before Peter, James, and John, revealing his divine glory.',
        scripture: 'Matthew 17:1–9',
        fruit: 'Desire for Holiness',
      },
      {
        number: 5,
        title: 'The Institution of the Eucharist',
        meditation: 'Jesus gives himself to us in the Eucharist at the Last Supper.',
        scripture: 'Matthew 26:26–30',
        fruit: 'Eucharistic Adoration',
      },
    ],
  },
  sorrowful: {
    key: 'sorrowful',
    title: 'The Sorrowful Mysteries',
    shortName: 'Sorrowful',
    daysLabel: 'Tuesday & Friday',
    daysOfWeek: [2, 5], // 2 = Tuesday, 5 = Friday
    description: 'Meditate upon the Passion and Death of Our Lord Jesus Christ for the redemption of the world.',
    colorAccent: '#7b1124',
    items: [
      {
        number: 1,
        title: 'The Agony in the Garden',
        meditation: 'Jesus prays in agony in the Garden of Gethsemane and submits to the Father’s will.',
        scripture: 'Matthew 26:36–46',
        fruit: 'Contrition for Sins & Conformity to God’s Will',
      },
      {
        number: 2,
        title: 'The Scourging at the Pillar',
        meditation: 'Jesus is scourged and suffers for the salvation of humanity.',
        scripture: 'Matthew 27:26',
        fruit: 'Mortification & Purity',
      },
      {
        number: 3,
        title: 'The Crowning with Thorns',
        meditation: 'Jesus is mocked and crowned with thorns.',
        scripture: 'Matthew 27:27–31',
        fruit: 'Moral Courage & Reparation',
      },
      {
        number: 4,
        title: 'The Carrying of the Cross',
        meditation: 'Jesus carries his Cross on the way to Calvary.',
        scripture: 'Luke 23:26–32',
        fruit: 'Patience & Bearing Our Crosses',
      },
      {
        number: 5,
        title: 'The Crucifixion',
        meditation: 'Jesus gives his life on the Cross for the salvation of the world.',
        scripture: 'Luke 23:33–49',
        fruit: 'Salvation, Forgiveness & Self-Offering',
      },
    ],
  },
  glorious: {
    key: 'glorious',
    title: 'The Glorious Mysteries',
    shortName: 'Glorious',
    daysLabel: 'Wednesday & Sunday',
    daysOfWeek: [0, 3], // 0 = Sunday, 3 = Wednesday
    description: 'Meditate upon the triumph of the Resurrection and the glory of Heaven.',
    colorAccent: '#c89b3c',
    items: [
      {
        number: 1,
        title: 'The Resurrection',
        meditation: 'Jesus rises from the dead, conquering sin and death.',
        scripture: 'Matthew 28:1–10',
        fruit: 'Faith & Newness of Life',
      },
      {
        number: 2,
        title: 'The Ascension',
        meditation: 'Jesus ascends into Heaven and returns to the Father.',
        scripture: 'Acts 1:6–11',
        fruit: 'Hope & Longing for Heaven',
      },
      {
        number: 3,
        title: 'The Descent of the Holy Spirit',
        meditation: 'The Holy Spirit descends upon Mary and the Apostles at Pentecost.',
        scripture: 'Acts 2:1–13',
        fruit: 'Wisdom & Love of God',
      },
      {
        number: 4,
        title: 'The Assumption of Mary',
        meditation: 'Mary is taken body and soul into heavenly glory.',
        scripture: 'Munificentissimus Deus',
        fruit: 'Devotion to Mary & Grace of a Happy Death',
      },
      {
        number: 5,
        title: 'The Coronation of Mary',
        meditation: 'Mary is crowned Queen of Heaven and Earth.',
        scripture: 'Revelation 12:1',
        fruit: 'Trust in Mary’s Intercession',
      },
    ],
  },
}

export const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']

/**
 * Weekly traditional schedule mapping
 * Sunday (0): Glorious
 * Monday (1): Joyful
 * Tuesday (2): Sorrowful
 * Wednesday (3): Glorious
 * Thursday (4): Luminous
 * Friday (5): Sorrowful
 * Saturday (6): Joyful
 */
export const weeklyMysterySchedule = [
  { dayIndex: 0, dayName: 'Sunday', mysteryKey: 'glorious' },
  { dayIndex: 1, dayName: 'Monday', mysteryKey: 'joyful' },
  { dayIndex: 2, dayName: 'Tuesday', mysteryKey: 'sorrowful' },
  { dayIndex: 3, dayName: 'Wednesday', mysteryKey: 'glorious' },
  { dayIndex: 4, dayName: 'Thursday', mysteryKey: 'luminous' },
  { dayIndex: 5, dayName: 'Friday', mysteryKey: 'sorrowful' },
  { dayIndex: 6, dayName: 'Saturday', mysteryKey: 'joyful' },
]

export const getTodayMysteryKey = (date = new Date()) => {
  const day = date.getDay()
  const found = weeklyMysterySchedule.find(s => s.dayIndex === day)
  return found ? found.mysteryKey : 'joyful'
}

export const getTodayDayName = (date = new Date()) => {
  return dayNames[date.getDay()]
}

/**
 * Generate complete 33 steps for interactive Rosary prayer companion
 */
export const generateRosarySteps = (mysteryKey = 'joyful') => {
  const mysterySet = rosaryMysteries[mysteryKey] || rosaryMysteries.joyful
  const mysteries = mysterySet.items

  return [
    // 1. Sign of the Cross
    {
      stepIndex: 1,
      part: 'Introductory Prayers',
      sectionType: 'intro',
      decade: 0,
      title: 'Sign of the Cross',
      instruction: 'Hold the Crucifix in your hand and make the Sign of the Cross reverently.',
      prayerKey: 'signOfTheCross',
      prayerText: rosaryPrayers.signOfTheCross.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Crucifix',
    },
    // 2. Apostles' Creed
    {
      stepIndex: 2,
      part: 'Introductory Prayers',
      sectionType: 'intro',
      decade: 0,
      title: "The Apostles' Creed",
      instruction: 'Holding the Crucifix, profess the foundational truths of the Catholic faith.',
      prayerKey: 'apostlesCreed',
      prayerText: rosaryPrayers.apostlesCreed.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Crucifix',
    },
    // 3. Our Father
    {
      stepIndex: 3,
      part: 'Introductory Prayers',
      sectionType: 'intro',
      decade: 0,
      title: "The Lord's Prayer (Our Father)",
      instruction: 'Move to the first large bead above the Crucifix and pray for the intentions of our Holy Father.',
      prayerKey: 'ourFather',
      prayerText: rosaryPrayers.ourFather.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'First Large Bead',
    },
    // 4. Three Hail Marys
    {
      stepIndex: 4,
      part: 'Introductory Prayers',
      sectionType: 'intro',
      decade: 0,
      title: 'Three Hail Marys',
      instruction: 'Pray three Hail Marys for an increase of faith, hope, and charity across the next three small beads.',
      prayerKey: 'hailMary',
      prayerText: rosaryPrayers.hailMary.text,
      beadCount: 3,
      totalBeads: 3,
      beadLabel: 'Three Small Beads',
      repetitions: [
        { count: 1, intention: 'For an increase in Faith' },
        { count: 2, intention: 'For an increase in Hope' },
        { count: 3, intention: 'For an increase in Charity' },
      ],
    },
    // 5. Glory Be
    {
      stepIndex: 5,
      part: 'Introductory Prayers',
      sectionType: 'intro',
      decade: 0,
      title: 'Glory Be',
      instruction: 'On the chain preceding the medal, praise the Most Holy Trinity.',
      prayerKey: 'gloryBe',
      prayerText: rosaryPrayers.gloryBe.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Chain before Medal',
    },

    // DECADE 1
    // 6. First Mystery
    {
      stepIndex: 6,
      part: 'First Decade',
      sectionType: 'mystery',
      decade: 1,
      title: `1st Mystery: ${mysteries[0].title}`,
      mysteryNumber: 1,
      mysteryTitle: mysteries[0].title,
      meditation: mysteries[0].meditation,
      scripture: mysteries[0].scripture,
      fruit: mysteries[0].fruit,
      instruction: 'Announce the First Mystery and take a moment of silent prayerful meditation.',
      isMysteryCard: true,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 1 Medal / Large Bead',
    },
    // 7. Decade 1 Our Father
    {
      stepIndex: 7,
      part: 'First Decade',
      sectionType: 'our-father',
      decade: 1,
      title: "The Lord's Prayer (Our Father)",
      instruction: 'Pray on the large bead of the First Decade.',
      prayerKey: 'ourFather',
      prayerText: rosaryPrayers.ourFather.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 1 Large Bead',
    },
    // 8. Decade 1 Ten Hail Marys
    {
      stepIndex: 8,
      part: 'First Decade',
      sectionType: 'hail-marys',
      decade: 1,
      title: 'Ten Hail Marys',
      instruction: `Meditate upon ${mysteries[0].title} as you pray 10 Hail Marys on each bead of the decade.`,
      prayerKey: 'hailMary',
      prayerText: rosaryPrayers.hailMary.text,
      beadCount: 10,
      totalBeads: 10,
      beadLabel: '10 Decade Beads',
      currentMystery: mysteries[0],
    },
    // 9. Decade 1 Glory Be
    {
      stepIndex: 9,
      part: 'First Decade',
      sectionType: 'glory-be',
      decade: 1,
      title: 'Glory Be',
      instruction: 'Praise the Blessed Trinity on the chain following the tenth bead.',
      prayerKey: 'gloryBe',
      prayerText: rosaryPrayers.gloryBe.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Chain after Decade 1',
    },
    // 10. Decade 1 Fatima Prayer
    {
      stepIndex: 10,
      part: 'First Decade',
      sectionType: 'fatima',
      decade: 1,
      title: 'Fatima Prayer',
      instruction: 'Conclude the First Decade with the prayer requested by Our Lady at Fatima.',
      prayerKey: 'fatimaPrayer',
      prayerText: rosaryPrayers.fatimaPrayer.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 1 Conclusion',
    },

    // DECADE 2
    // 11. Second Mystery
    {
      stepIndex: 11,
      part: 'Second Decade',
      sectionType: 'mystery',
      decade: 2,
      title: `2nd Mystery: ${mysteries[1].title}`,
      mysteryNumber: 2,
      mysteryTitle: mysteries[1].title,
      meditation: mysteries[1].meditation,
      scripture: mysteries[1].scripture,
      fruit: mysteries[1].fruit,
      instruction: 'Announce the Second Mystery and meditate upon the passage.',
      isMysteryCard: true,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 2 Large Bead',
    },
    // 12. Decade 2 Our Father
    {
      stepIndex: 12,
      part: 'Second Decade',
      sectionType: 'our-father',
      decade: 2,
      title: "The Lord's Prayer (Our Father)",
      instruction: 'Pray on the large bead of the Second Decade.',
      prayerKey: 'ourFather',
      prayerText: rosaryPrayers.ourFather.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 2 Large Bead',
    },
    // 13. Decade 2 Ten Hail Marys
    {
      stepIndex: 13,
      part: 'Second Decade',
      sectionType: 'hail-marys',
      decade: 2,
      title: 'Ten Hail Marys',
      instruction: `Meditate upon ${mysteries[1].title} as you pray 10 Hail Marys.`,
      prayerKey: 'hailMary',
      prayerText: rosaryPrayers.hailMary.text,
      beadCount: 10,
      totalBeads: 10,
      beadLabel: '10 Decade Beads',
      currentMystery: mysteries[1],
    },
    // 14. Decade 2 Glory Be
    {
      stepIndex: 14,
      part: 'Second Decade',
      sectionType: 'glory-be',
      decade: 2,
      title: 'Glory Be',
      instruction: 'Praise the Blessed Trinity on the chain.',
      prayerKey: 'gloryBe',
      prayerText: rosaryPrayers.gloryBe.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Chain after Decade 2',
    },
    // 15. Decade 2 Fatima Prayer
    {
      stepIndex: 15,
      part: 'Second Decade',
      sectionType: 'fatima',
      decade: 2,
      title: 'Fatima Prayer',
      instruction: 'Conclude the Second Decade with the Fatima Prayer.',
      prayerKey: 'fatimaPrayer',
      prayerText: rosaryPrayers.fatimaPrayer.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 2 Conclusion',
    },

    // DECADE 3
    // 16. Third Mystery
    {
      stepIndex: 16,
      part: 'Third Decade',
      sectionType: 'mystery',
      decade: 3,
      title: `3rd Mystery: ${mysteries[2].title}`,
      mysteryNumber: 3,
      mysteryTitle: mysteries[2].title,
      meditation: mysteries[2].meditation,
      scripture: mysteries[2].scripture,
      fruit: mysteries[2].fruit,
      instruction: 'Announce the Third Mystery and meditate on Christ’s holy presence.',
      isMysteryCard: true,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 3 Large Bead',
    },
    // 17. Decade 3 Our Father
    {
      stepIndex: 17,
      part: 'Third Decade',
      sectionType: 'our-father',
      decade: 3,
      title: "The Lord's Prayer (Our Father)",
      instruction: 'Pray on the large bead of the Third Decade.',
      prayerKey: 'ourFather',
      prayerText: rosaryPrayers.ourFather.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 3 Large Bead',
    },
    // 18. Decade 3 Ten Hail Marys
    {
      stepIndex: 18,
      part: 'Third Decade',
      sectionType: 'hail-marys',
      decade: 3,
      title: 'Ten Hail Marys',
      instruction: `Meditate upon ${mysteries[2].title} across the 10 beads.`,
      prayerKey: 'hailMary',
      prayerText: rosaryPrayers.hailMary.text,
      beadCount: 10,
      totalBeads: 10,
      beadLabel: '10 Decade Beads',
      currentMystery: mysteries[2],
    },
    // 19. Decade 3 Glory Be
    {
      stepIndex: 19,
      part: 'Third Decade',
      sectionType: 'glory-be',
      decade: 3,
      title: 'Glory Be',
      instruction: 'Praise the Blessed Trinity on the chain.',
      prayerKey: 'gloryBe',
      prayerText: rosaryPrayers.gloryBe.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Chain after Decade 3',
    },
    // 20. Decade 3 Fatima Prayer
    {
      stepIndex: 20,
      part: 'Third Decade',
      sectionType: 'fatima',
      decade: 3,
      title: 'Fatima Prayer',
      instruction: 'Conclude the Third Decade with the Fatima Prayer.',
      prayerKey: 'fatimaPrayer',
      prayerText: rosaryPrayers.fatimaPrayer.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 3 Conclusion',
    },

    // DECADE 4
    // 21. Fourth Mystery
    {
      stepIndex: 21,
      part: 'Fourth Decade',
      sectionType: 'mystery',
      decade: 4,
      title: `4th Mystery: ${mysteries[3].title}`,
      mysteryNumber: 4,
      mysteryTitle: mysteries[3].title,
      meditation: mysteries[3].meditation,
      scripture: mysteries[3].scripture,
      fruit: mysteries[3].fruit,
      instruction: 'Announce the Fourth Mystery and reflect upon its grace.',
      isMysteryCard: true,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 4 Large Bead',
    },
    // 22. Decade 4 Our Father
    {
      stepIndex: 22,
      part: 'Fourth Decade',
      sectionType: 'our-father',
      decade: 4,
      title: "The Lord's Prayer (Our Father)",
      instruction: 'Pray on the large bead of the Fourth Decade.',
      prayerKey: 'ourFather',
      prayerText: rosaryPrayers.ourFather.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 4 Large Bead',
    },
    // 23. Decade 4 Ten Hail Marys
    {
      stepIndex: 23,
      part: 'Fourth Decade',
      sectionType: 'hail-marys',
      decade: 4,
      title: 'Ten Hail Marys',
      instruction: `Meditate upon ${mysteries[3].title} as you pray 10 Hail Marys.`,
      prayerKey: 'hailMary',
      prayerText: rosaryPrayers.hailMary.text,
      beadCount: 10,
      totalBeads: 10,
      beadLabel: '10 Decade Beads',
      currentMystery: mysteries[3],
    },
    // 24. Decade 4 Glory Be
    {
      stepIndex: 24,
      part: 'Fourth Decade',
      sectionType: 'glory-be',
      decade: 4,
      title: 'Glory Be',
      instruction: 'Praise the Blessed Trinity on the chain.',
      prayerKey: 'gloryBe',
      prayerText: rosaryPrayers.gloryBe.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Chain after Decade 4',
    },
    // 25. Decade 4 Fatima Prayer
    {
      stepIndex: 25,
      part: 'Fourth Decade',
      sectionType: 'fatima',
      decade: 4,
      title: 'Fatima Prayer',
      instruction: 'Conclude the Fourth Decade with the Fatima Prayer.',
      prayerKey: 'fatimaPrayer',
      prayerText: rosaryPrayers.fatimaPrayer.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 4 Conclusion',
    },

    // DECADE 5
    // 26. Fifth Mystery
    {
      stepIndex: 26,
      part: 'Fifth Decade',
      sectionType: 'mystery',
      decade: 5,
      title: `5th Mystery: ${mysteries[4].title}`,
      mysteryNumber: 5,
      mysteryTitle: mysteries[4].title,
      meditation: mysteries[4].meditation,
      scripture: mysteries[4].scripture,
      fruit: mysteries[4].fruit,
      instruction: 'Announce the Fifth Mystery and enter into deep thanksgiving.',
      isMysteryCard: true,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 5 Large Bead',
    },
    // 27. Decade 5 Our Father
    {
      stepIndex: 27,
      part: 'Fifth Decade',
      sectionType: 'our-father',
      decade: 5,
      title: "The Lord's Prayer (Our Father)",
      instruction: 'Pray on the large bead of the Fifth Decade.',
      prayerKey: 'ourFather',
      prayerText: rosaryPrayers.ourFather.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 5 Large Bead',
    },
    // 28. Decade 5 Ten Hail Marys
    {
      stepIndex: 28,
      part: 'Fifth Decade',
      sectionType: 'hail-marys',
      decade: 5,
      title: 'Ten Hail Marys',
      instruction: `Meditate upon ${mysteries[4].title} as you complete the final decade.`,
      prayerKey: 'hailMary',
      prayerText: rosaryPrayers.hailMary.text,
      beadCount: 10,
      totalBeads: 10,
      beadLabel: '10 Decade Beads',
      currentMystery: mysteries[4],
    },
    // 29. Decade 5 Glory Be
    {
      stepIndex: 29,
      part: 'Fifth Decade',
      sectionType: 'glory-be',
      decade: 5,
      title: 'Glory Be',
      instruction: 'Praise the Blessed Trinity on the chain.',
      prayerKey: 'gloryBe',
      prayerText: rosaryPrayers.gloryBe.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Chain after Decade 5',
    },
    // 30. Decade 5 Fatima Prayer
    {
      stepIndex: 30,
      part: 'Fifth Decade',
      sectionType: 'fatima',
      decade: 5,
      title: 'Fatima Prayer',
      instruction: 'Conclude the Fifth Decade with the Fatima Prayer.',
      prayerKey: 'fatimaPrayer',
      prayerText: rosaryPrayers.fatimaPrayer.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Decade 5 Conclusion',
    },

    // CONCLUDING PRAYERS
    // 31. Hail, Holy Queen
    {
      stepIndex: 31,
      part: 'Concluding Prayers',
      sectionType: 'concluding',
      decade: 5,
      title: 'Hail, Holy Queen',
      instruction: 'Holding the holy medal or rosary, turn in filial confidence to Mary, Queen and Mother of Mercy.',
      prayerKey: 'hailHolyQueen',
      prayerText: rosaryPrayers.hailHolyQueen.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Rosary Medal',
    },
    // 32. Closing Rosary Prayer
    {
      stepIndex: 32,
      part: 'Concluding Prayers',
      sectionType: 'concluding',
      decade: 5,
      title: 'Closing Rosary Prayer',
      instruction: 'Pray the concluding oration, uniting our meditations with Christ’s promise of eternal life.',
      prayerKey: 'closingPrayer',
      prayerText: rosaryPrayers.closingPrayer.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Rosary Medal',
    },
    // 33. Sign of the Cross
    {
      stepIndex: 33,
      part: 'Concluding Prayers',
      sectionType: 'concluding',
      decade: 5,
      title: 'The Sign of the Cross',
      instruction: 'Reverently make the Sign of the Cross to conclude the Holy Rosary.',
      prayerKey: 'signOfTheCross',
      prayerText: rosaryPrayers.signOfTheCross.text,
      beadCount: 1,
      totalBeads: 1,
      beadLabel: 'Crucifix',
    },
  ]
}

