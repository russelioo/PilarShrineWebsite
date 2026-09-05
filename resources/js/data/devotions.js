/**
 * Official Devotional Resources Data Source
 * Diocesan Shrine and Parish of Our Lady of the Pillar
 * Pilar, Sorsogon
 *
 * ONE SOURCE OF TRUTH for official prayers and hymns.
 * All lyrics, stanzas, and composer metadata are preserved verbatim.
 */

export const devotionalResources = [
  {
    id: 'prayer-to-our-lady-of-the-pillar',
    type: 'prayer',
    category: 'Official Shrine Prayer',
    title: 'Prayer to Our Lady of the Pillar',
    subtitle: 'Pamibi ki Nuestra Señora del Pilar',
    composer: null,
    metadata: 'Diocesan Shrine & Parish of Our Lady of the Pillar',
    shortDescription: 'The official prayer of entrustment, family protection, and maternal intercession to Nuestra Señora del Pilar.',
    actionLabel: 'Read Prayer',
    sections: [
      {
        heading: 'Prayer to Our Lady of the Pillar (English)',
        isChorus: false,
        lines: [
          'Most Holy Virgin of the Pillar,',
          'to your care we confide the needs of our families,',
          'the joys of children, the dreams of youth,',
          'the anxieties of adults, the sufferings of the sick,',
          'and our preparation for life\'s end.',
          '',
          'To you we entrust the perseverance of our priests and religious.',
          '',
          'Ever increase our faith,',
          'give assurance to our hope,',
          'and enliven our charity.',
          '',
          'Amen.',
          '',
          'Our Lady of the Pillar, pray for us.',
        ],
      },
      {
        heading: 'Pamibi ki Nuestra Señora del Pilar (Bikol)',
        isChorus: false,
        lines: [
          'Makakamhan asin daing katapusan na Dios,',
          'na itinogot mo, na an saimong Mahal na Ina',
          'si Virgen Maria ibugtak, kan siya uya pa sa kinaban,',
          'sa sarong moog na marmol nin tolong coro nin mga angeles,',
          'na magin niamong paalawan;',
          '',
          'magdalita ka, O Kagurangnan na itogot mo samuya,',
          'huli kan saiyang kaogdan asin mga pagngayongayo,',
          'an sa malaad na pagmawot,',
          'hinahagad niamo saimo sa saiyang ngaran,',
          'Amen.',
          '',
          'O Maria, Virgen sa Moog,',
          'Ipamibi mo kami!',
        ],
      },
    ],
    rawText: `Most Holy Virgin of the Pillar,
to your care we confide the needs of our families,
the joys of children, the dreams of youth,
the anxieties of adults, the sufferings of the sick,
and our preparation for life's end.

To you we entrust the perseverance of our priests and religious.

Ever increase our faith,
give assurance to our hope,
and enliven our charity.

Amen.

Our Lady of the Pillar, pray for us.

---

Makakamhan asin daing katapusan na Dios,
na itinogot mo, na an saimong Mahal na Ina
si Virgen Maria ibugtak, kan siya uya pa sa kinaban,
sa sarong moog na marmol nin tolong coro nin mga angeles,
na magin niamong paalawan;

magdalita ka, O Kagurangnan na itogot mo samuya,
huli kan saiyang kaogdan asin mga pagngayongayo,
an sa malaad na pagmawot,
hinahagad niamo saimo sa saiyang ngaran,
Amen.

O Maria, Virgen sa Moog,
Ipamibi mo kami!`,
  },
  {
    id: 'nuestra-senora-del-pilar-hymn',
    type: 'hymn',
    category: 'Marian Hymn',
    title: 'Nuestra Señora del Pilar Hymn',
    subtitle: null,
    composer: 'Joel G. France (1988)',
    ref: 'Nuestra Señora del Pilar',
    metadata: 'Composed by Joel G. France (1988)',
    shortDescription: 'Official parish hymn honoring Nuestra Señora del Pilar as our guide and refuge across life’s journey.',
    actionLabel: 'View Lyrics',
    sections: [
      {
        heading: null,
        isChorus: false,
        lines: [
          'Nuestra Señora del Pilar',
          '',
          'Ika an samong paalawan',
          '',
          'Kami pakaingatan',
          '',
          'Agawon sa karatan.',
          '',
          'Maliwanag siring sa aldaw',
          '',
          'An samong nahiling',
          '',
          'Mantang sa tahaw nin dagat',
          '',
          'Kami pasiring sa kamurawayan',
          '',
          'Nin paglalakbay.',
        ],
      },
    ],
    rawText: `Nuestra Señora del Pilar

Ika an samong paalawan

Kami pakaingatan

Agawon sa karatan.

Maliwanag siring sa aldaw

An samong nahiling

Mantang sa tahaw nin dagat

Kami pasiring sa kamurawayan

Nin paglalakbay.`,
  },
  {
    id: 'himno-nuestra-senora-del-pilar',
    type: 'hymn',
    category: 'Solemn Hymn',
    title: 'Himno a la Nuestra Señora del Pilar',
    subtitle: null,
    composer: 'John Oliver Mimay',
    metadata: 'Composed by John Oliver Mimay',
    shortDescription: 'A bilingual Spanish-Bikol hymn of praise, veneration, and blessing embracing the sacred Pillar.',
    actionLabel: 'View Lyrics',
    sections: [
      {
        heading: null,
        isChorus: false,
        lines: [
          'La Virgen Santa',
          '',
          'Madre mia',
          '',
          'La Luz Hermosa',
          '',
          'Claro dia',
          '',
          'Que la tierra pilareña',
          '',
          'Te dignaste visitar',
          '',
          'Este pueblo que te adora',
          '',
          'De tu amor favor Implora',
          '',
          'Te aclama y bendice',
          '',
          'Abrazado a tu Pilar',
          '',
          'O banal na harigi',
          '',
          'Harong na maliwanag',
          '',
          'Tunay na biyaya ka',
          '',
          'Kan pagkamoot kan Dios Ama',
          '',
          'O Santang Virgen ina niamo',
          '',
          'Marhay na ilaw',
          '',
          'Kailiwanagan',
          '',
          'Sa Pilar samuyang banwaan',
          '',
          'Kabotan mong erokan',
          '',
          'Banwaan na si’mo nag-aarang',
          '',
          'Naghahagad na si’mong kamotan',
          '',
          'Mamuraway kang bendicionan',
          '',
          'Nuestra Señora del Pilar',
          '',
          'Paladan na harigi',
          '',
          'Trono na mamuraway',
          '',
          'Samo ika’n magiya',
          '',
          'Sa kahadean nin Dios Ama',
          '',
          'La Virgen Santa',
          '',
          'Madre mia',
          '',
          'La Luz Hermosa',
          '',
          'Claro dia',
          '',
          'Que la tierra pilareña',
          '',
          'Te dignaste visitar',
          '',
          'Este pueblo que te adora',
          '',
          'De tu amor favor Implora',
          '',
          'Te aclama y bendice',
          '',
          'Abrazado a tu Pilar',
          '',
          'Pagawitan ka sagkod pa man',
          '',
          'Nuestra Señora del Pilar',
        ],
      },
    ],
    rawText: `La Virgen Santa

Madre mia

La Luz Hermosa

Claro dia

Que la tierra pilareña

Te dignaste visitar

Este pueblo que te adora

De tu amor favor Implora

Te aclama y bendice

Abrazado a tu Pilar

O banal na harigi

Harong na maliwanag

Tunay na biyaya ka

Kan pagkamoot kan Dios Ama

O Santang Virgen ina niamo

Marhay na ilaw

Kailiwanagan

Sa Pilar samuyang banwaan

Kabotan mong erokan

Banwaan na si’mo nag-aarang

Naghahagad na si’mong kamotan

Mamuraway kang bendicionan

Nuestra Señora del Pilar

Paladan na harigi

Trono na mamuraway

Samo ika’n magiya

Sa kahadean nin Dios Ama

La Virgen Santa

Madre mia

La Luz Hermosa

Claro dia

Que la tierra pilareña

Te dignaste visitar

Este pueblo que te adora

De tu amor favor Implora

Te aclama y bendice

Abrazado a tu Pilar

Pagawitan ka sagkod pa man

Nuestra Señora del Pilar`,
  },
  {
    id: 'balaog-asin-biyaya',
    type: 'hymn',
    category: 'Episcopal Coronation Hymn',
    title: 'Balaog Asin Biyaya',
    subtitle: 'A Hymn to Our Lady of the Pillar on Her Solemn Episcopal Coronation',
    composer: 'John Oliver Mimay',
    metadata: 'Composed by John Oliver Mimay',
    shortDescription: 'Official coronation hymn celebrating Mary as gift and grace (balaog asin biyaya) to the town of Pilar.',
    actionLabel: 'View Lyrics',
    sections: [
      {
        heading: 'I',
        isChorus: false,
        lines: [
          'Nagmaimbod kang sorogoon',
          '',
          'sa pagsunod sa pagboot Diosnon.',
          '',
          'Mapakumbaba mong inako, pano nin kaogmahan,',
          '',
          'na magin ina nin satong Kagurangnan.',
        ],
      },
      {
        heading: 'II',
        isChorus: false,
        lines: [
          'Nagmaigot mong tinalikdan,',
          '',
          'Mga kinabanon na kayamanan.',
          '',
          'Mapusog kang nanindugan, pano nin pagkamoot,',
          '',
          'Kan si Kristo nagadan duman sa Krus.',
        ],
      },
      {
        heading: 'KORO',
        isChorus: true,
        lines: [
          'Balaog ka asin biyaya, mahal niamong Ina,',
          '',
          'Nuestra Señora del Pilar!',
          '',
          'Sa bilog niamong banwaan,',
          '',
          'Ika an naging paalawan,',
          '',
          'balaog asin biyaya, sagkod pa man!',
        ],
      },
      {
        heading: 'III',
        isChorus: false,
        lines: [
          'Nagsasarig ming hinahagad,',
          '',
          'na maarog mi si\'mong kaimbodan.',
          '',
          'Na posog mi man na simbagon, pano nin pagtubod,',
          '',
          'pangapodan kan Dios na magin lingkod.',
        ],
      },
      {
        heading: 'KORO I',
        isChorus: true,
        lines: [
          'Balaog ka asin biyaya, mahal niamong Ina,',
          '',
          'Nuestra Señora del Pilar!',
          '',
          'Sa bilog niamong banwaan,',
          '',
          'Ika an naging paalawan,',
          '',
          'balaog asin biyaya, sagkod pa man!',
        ],
      },
      {
        heading: 'KORO II',
        isChorus: true,
        lines: [
          'Balaog ka asin biyaya, mahal niamong Ina,',
          '',
          'Nuestra Señora del Pilar!',
          '',
          'Sa bilog niamong banwaan,',
          '',
          'Ika an naging paalawan,',
          '',
          'balaog asin biyaya,',
          '',
          'balaog asin biyaya,',
          '',
          'balaog asin biyaya, sagkod pa man!',
        ],
      },
    ],
    rawText: `I

Nagmaimbod kang sorogoon

sa pagsunod sa pagboot Diosnon.

Mapakumbaba mong inako, pano nin kaogmahan,

na magin ina nin satong Kagurangnan.


II

Nagmaigot mong tinalikdan,

Mga kinabanon na kayamanan.

Mapusog kang nanindugan, pano nin pagkamoot,

Kan si Kristo nagadan duman sa Krus.


KORO

Balaog ka asin biyaya, mahal niamong Ina,

Nuestra Señora del Pilar!

Sa bilog niamong banwaan,

Ika an naging paalawan,

balaog asin biyaya, sagkod pa man!


III

Nagsasarig ming hinahagad,

na maarog mi si'mong kaimbodan.

Na posog mi man na simbagon, pano nin pagtubod,

pangapodan kan Dios na magin lingkod.


KORO I

Balaog ka asin biyaya, mahal niamong Ina,

Nuestra Señora del Pilar!

Sa bilog niamong banwaan,

Ika an naging paalawan,

balaog asin biyaya, sagkod pa man!


KORO II

Balaog ka asin biyaya, mahal niamong Ina,

Nuestra Señora del Pilar!

Sa bilog niamong banwaan,

Ika an naging paalawan,

balaog asin biyaya,

balaog asin biyaya,

balaog asin biyaya, sagkod pa man!`,
  },
]
