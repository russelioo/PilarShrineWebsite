<script setup>
import { computed, ref } from 'vue'

const gaweNinPagsolsol = `O Hesukristong Kagurangnan, Dios na totoo asin tawo man na totoo, Kaglalang, Ama asin Paratubos ko. Ika, huli kan saimong sinaglitan na pagka-Diyos asin banal na pagkatawo, namomotan ko nin orog sa gabos, asin ikinahohorasa ko, O Kagurangnan, an sakong pagkasala saimo. Naghoholaw ako, Diyos ko, na dai na magkakasala liwat asin nin paglikay sa gabos na mga pag-alaman nin pagkasala. Naghuhulaw ako nin pagkamoot asin nin paglingkod saimo sagkod sa katapusan nin sakuyang buhay. Awot pa.`

const gozos = [
  `Lubos an samong pagsarig\nNa kami man magkakamit\nBirhen na orog kalinig\nKan grasya nin Dios sa langit\nMapangyari mong pagtabang.\nHalat niamo sa giraray.`,
  `O maugayon na Reyna\nNa ginaha kan Diyos Ama\nBirhen na daing iba\nAn kanoro mi o giya;\nHilnga panong pag-alaman,\nDigdi an samong paglakbay.`,
  `Likos nin mga panganib\nDalan pasiring sa langit\nMahalpas asin balakid\nMay ampas sa lambang gilid\nBirhen kami mo ibanhan\nAn samuya na paglakbay.`,
  `Kan uya pa ikang tunay\nSa ibabaw nin kinaban\nIka, Birhen napaduman\nSa Españang kahadean,\nDara nin ka-anghelesan\nSa moog na ngangalasan.`,
  `Sa panahon na halawig\nNi Santiago sa paghulit\nMayo lamang ni kadikit\nMga boot na nabirik\nSi mga Kastila solang\nSa bago na katinubdan.`,
  `Puso nin mga Kastila\nMatatagas, samong Ina;\nKaya, nag-iisip na kuta\nSi Santiago nin pagbaya;\nNapaduman, tuminukaw,\nSalog sa Ebro sa pangpang.`,
  `Sayang ngaya an paghulit\nNi Santiago an sa isip\nHabo sinda na mabirik\nSa grasya nin Dios sa langit\nAn biko na katinubdan\nDi boot nindang talikdan.`,
  `Mga matagas na boot\nNgapit an mabalangibog\nKan tunay na pagtubod\nSa kinaban na bilog\nAn totoong katinubdan\nSa Espanya di makulang.`,
  `Tanganing dai magluya\nBoot ni Santiago baga\nO Birhen ika dinara\nNin angheles sa Espanya\nSaimo siyang sinabihan\nEspanya di pabayaan.`,
  `Ki Santiago ipigboot\nNin simbahan magpatindog\nSa pangpang kaidtong salog\nNa Ebro iyo an apod\nPinatindog daing liban\nAn togon mo na simbahan.`,
  `Huli sa saimong Moog\nDakul nabirik na boot\nSa tunay na pagtubod\nSa kasalan nagtalikod\nDarakulang ngangalasan\nIpinakita mong tunay.`,
  `An samuyang puso, boot,\nO Birhen, tunay na moog\nDigdi nin Diyos pinatindog\nAn tunay nganing pagtubod\nParigona sa giraray\nIna, an moog na iyan.`,
  `Birhen, dai mo itogot\nNa mapukan ining moog\nNin samuya na pagtubod\nNgonyan baga natatanyog\nTiripona man giraray\nSa Santa Iglesya an suhay.`,
  `Daghan nin lambang Bikolnon\nSarong templo na magayon\nMoog man na masarigon\nAn puso nin taga-Bicol\nSa templo, moog na iyan\nYaon an simong ladawan.`,
]

const gozosResponse = 'Ika samong inoomaw, Nuestra Señora del Pilar.'

const prayers = [
  {
    day: 'Enot na Aldaw', date: 'October 3',
    text: `Tara, Kagurangnan Maria, Princesa asin minamahal na ginaha nin Amang daing katapusan, iyo-iyong pinili sa gabos na mga aki ni Eva, pinili kan kaenot-enote pang mga panahon na maging saiyang Aking para-orog. Tara, mabansay na doncella, na ilinikay sa kasalan na orihinal magpoon kan pagtudkan pa sana saimo sa tulak. Tara Kagurangnan Maria, masagang Moog, na siring sa sarong salming pinaghehelingan sa saiya man sana kan Makakamhan.

Nagngangayo-ngayo kami saimo, O Ina niamo sa Moog, dara an gikan sa pusong kahuyuan asin an bulanos na pagsolsol na hihingnuogon mo an saimong mga devotos asin gamponon mo sinda sa atubang nin Dios. Gakopon mo kami, maglinig asin iligtas sa ano man na digta nin kasalan. Itogot samuya, O Mahamis na Señora, an biyaya na samuyang hinahagad sa novenang ini kun igo sa orog na kaomawan asin karahayan nin sakuyang kalag.

Nuestra Señora del Pilar, ipamibi mo kami. Amen.`
  },
  {
    day: 'Ikaduwang Aldaw', date: 'October 4',
    text: `Tara, Kagurangnan Maria, karapatdapat na gayo na magin Ina kan orog karahay sa gabos na mga aki si Hesukristo, Kagurangnan ta. An gabos na linalang maggaya-gaya logod sa saimong pagkamundag. O mahamis na Señora, samuyang Ina sa Moog, huli kaiyan nagilantas an kinaban, na iyong pinagmawot kan samuyang ginikanan na saindang makita asin iyo an tanda nin katiwasayan.

O posog na gayong Harigi, na naghaharubay kan nagdadangadang na lakop na pagkaolian liwat kan boot nin Diyos asin nin mga tawo gabos, na nagbabareta sa gabos na mga daing palad na mga aki ni Eva kan pakatalingkas sa mga talikala asin gapos nin kasalan. Mantang ika an Ina kan Berbo nin Diyos, ipagngayo-ngayo mo sinda asin pangenotan sa dalan pasiring sa saindang daing katapusan na katalingkasan.

Lilingya kami, O maugayon na Ina niamo sa Moog. Ipagngayo-ngayo mo kami huli kan misteryo nin saimong pagkamundag, magkamit logod kami nin kadikit na silyab kan saimong Diyosnon na kadunungan tanganing masabutan niamo an samong maraot na pag-isip asin baklion niamo an samuyang mga gawe-gawe. Itogot samuya an biyaya na samuyang hinahagad sa novenang ini. Awot pa.`
  },
  {
    day: 'Ikatolong Aldaw', date: 'October 5',
    text: `Tara, Kagurangnan Maria, esposa kan Espiritu Santo, ika sana an sinamnohan kan gabos na mga langitnon na balaog sa karahayan kan saimong mga aki. Ini saro sa mga saimong orog kasinanglitan asin mahamis na gayong ngaran, O Birhen Maria. An ngaran na ini minakunog-kunog sa bilog na impiyerno, pinapalayas an mga demonyo, minatawo nin kagaya-gayahan sa mga langitnon asin pinaparigon an boot nin bilog na katawohan sa maogmang pagdara kan managom na gabat kan mga togon nin Diyos asin sa may pandaog na pakilaban sa mga kapawotan nin pagkabuhay.

Naluhod kami sa atubang nin saimong trono na sa ina sa paggalang, O mahamis na Birhen del Pilar, asin hinahagad niyamo saimo an sarong dakula asin malodok na pagmahal sa saimong Mahal na Ngaran, na sa giraray iingatan niyamong nasusurat sa samuyang mga puso alintanang na isasabi iyan kan samuyang ngoso kaiba an malodok na debosyon. Awot pa.`
  },
  {
    day: 'Ikaapat na Aldaw', date: 'October 6',
    text: `Tara, Kagurangnan Maria, sinaglitan na Simbahan kan Santisima Trinidad. An mga linalang gabos nag-oomaw sa saimo huli kan iyo-iyong ining kapalaenan na iyo an balos sa saimong odok sa pusong kagalangan na isinamba mo sa Kagurangnan sa saiyang mahal na Simbahan duman sa Presentasyon.

Tara, Harigi nin mga angheles, nagpapasalamat kami saimo huli sa napapalaen na biyaya na itinao mo sa samo kan pilion mo an Simbahan na ini na simong napapalaen na templo, na ibinugtak sa saimong napapalaen na pintakasi asin sa irarom nin saimong napapalaen na pag-ampon, na digdi ika samuyang Reyna asin Pintakasi. Ika an nakakasadol samuya sa gabos na oras, an danay na paomay sa gabos niyamong kamatean sa kalag asin sa hawak.

Magdalita ka na iabot mo kami kan biyaya na magin kaming dayaday na buhay na mga templo kan Kahuruhalangkawe, saen maglaad sa giraray an sinanglitan na kalayo nin pagkamoot sa Diyos asin nin saiyang grasya. Itogot mo samuya an hinahagad mi saimo sa novenang ini, sunod sa sinanglitan na boot nin Diyos. Awot pa.`
  },
  {
    day: 'Ikalimang Aldaw', date: 'October 7',
    text: `Tara Kagurangnan Maria, esposang mabini kan orog katubis na si Jose, na kaibahan mo sa harong siring sa sarong tugang sa makuring pag-ingat asin sa dakulang kadayuputan inotob mo an sinanglitan na kapangakuan na kan ika saday pa ginibo mo sa atubang nin Diyos.

Tara, dai matanyog na Harigi nin kabanalan, tinios mong gayo an kahorasaan nin saimong puso kan dai pa ipinapahayag nin Diyos ki San Jose an mga darakulang misteryo na naririmpos saimo. Minsan natatalastas mo an pagkadai mo nin sala asin an kamahalan na binogtakan saimo kan mapangyaring kamot nin Diyos, nagtios kang daing girong, dara an dakulang panarig sa Diyos.

Nagngangayo-ngayo kami saimo, marigonon na gayong Ina, Birhen del Pilar, na pakusugon mo an samuyang mga puso, sa pagsunod sa saimong parawog, sa pagtios na madasig asin daing girong kan ano man na tukso asin mga pagtios na ipadara samuya kan kasuripotan nin Diyos. Awot pa.`
  },
  {
    day: 'Ikaanom na Aldaw', date: 'October 8',
    text: `Tara, Kagurangnan Maria, irinorokyaw na Reyna asin Señora nin gabos na mga linalang. Tara O Maria, kanigonigong trono asin lidkan kan daing tapos na Berbo nin Diyos. Sa saimo an Makakamhan kuminua nin sa tawong laman asin nagin samuyang tugang, an mapalad asin makangangalas na bunga kan saimong tulak, an Kagurangnan tang si Hesukristo.

Nagngangayo-ngayo kami saimo, O mamuraway na Ina sa Moog, huli kan mga kaogdan asin dai masabing misteryo kan Pagkanitawo, na papagkanigoon kaming magkaigwa nin kabtang sa daing sukol na bunga kan Pagtubos. Asin pakapagkumpisal niyamo kan samuyang mga kasalan, makabangon kami sa bagong buhay nin grasya asin magkanigo kaming magkamit saiya sa panahon na daing katapusan, sa kaibahan mo sa kamurawayan sa langit. Awot pa.`
  },
  {
    day: 'Ikapitong Aldaw', date: 'October 9',
    text: `Tara Kagurangnan Maria, dai matatanyog na Harigi kan satong Mahal na Katinubdan asin kan bilog na Iglesya Katolika. Huli sa saimong mapangyiring pagngayo-ngayo nadadaog niyamo an samong mga kaiwal, siring kan pandaog kan saimong Aki, si Hesukristo, Kagurangnan ta, sa gabos niyang mga kalwal huli kan saiyang mga sakit asin pagkagadan sa Krus.

O ika Birhen Maria, makangangalas asin mamuraway na Harigi kan lahi nin bilog na katawohan, huli kan saimong makangarigalas na Moog, an saimong Aking Diyosnon nagwawaras sa Iglesia Katolika nin danay na tabang asin nin pandaog sa gabos niyang mga kaiwal. Tara Kagurangnan Maria, Señora niamo asin samuyang Ina sa Moog, pinaorog karahay sa mga babae gabos, asin orog pakarahay an simong Aking si Jesus. Awot pa.`
  },
  {
    day: 'Ikawalong Aldaw', date: 'October 10',
    text: `Tara Kagurangnan Maria, Inang maheherakon asin maugayon na paalawan nin mga parakasala. Inoomaw ika kan gabos mong mga kampon huli kan saimong espiritu nin kapakumbabaan nin puso, na iinatubang mo sa simbahan siring sa sarong hamak na babae, sa pag-otob mo kan katugunan na dai man saimo nakakasakop, mantang ika an mayong digta asin malinigon na Ina nin Diyos. Sinunod mo an parawog kan saimong Aki na nagpakumbaba man huli sa ikakapalangit niyamo.

O Birhen del Pilar, mantang katabang ka sa Pagtubos, igwa ika nin kabtang sa mga pagtios asin mga kasopganan ni Hesukristo, Kagurangnan ta. Nagngangayo-ngayo kami saimo, O Ina niamo sa Moog, na lilingyon mo kami kan saimong mga matang maugayon, tanganing dai kami sumarungat sa matanos na dalan nin paglingkod sa Diyos. Panoa an samuyang mga puso nin daing kakundian na kahuyuan asin talanhok na kamunduan, patin kahorasaan huli kan pagkasala niyamo sa Diyos. Awot pa.`
  },
  {
    day: 'Ikasiyam na Aldaw', date: 'October 11',
    text: `Tara Kagurangnan Maria, paalawan asin karangahan sa lambang tukso asin kadalitaan. Tara Kagurangnan Maria, Señora asin Ina niyamo sa Moog, gapong ngangalasan na nakukuan nin kalag kan paumay saiya asin ibinabalik siya sa grasya. Tara Kagurangnan Maria, mapangyiring Reyna nin kalangitan asin nin kinaban, maugayon na Ina sa Moog.

Luhod sa pamitisan kan saimong mahal na Moog pinasasalamatan mi ika nin odok sa puso huli kan napapalaen na ining biyaya asin tanda nin saimong sa inang pagkamoot. Namimidbid niyamo an samuyang kabihangan, asin dara an pusong nagsosolsol nagpapahayag kami na minsan kami dai magkakanigo, hinahagad niyamo an saimong pagkaugay asin pagkaherak.

Huli sa saimong mapangyiring pagngayo-ngayo, iabot mo kami kan gabos na grasya na kaipuhan sa pakautob niyamo kan samuyang mga marhay na paghulaw asin paurani kan dagya mong mga biyaya, tanganing mabuhay kami sa sarong pagkabuhay na sono sa sinanglitan na boot nin Diyos asin makamtan niyamo siya sa panahon na daing kasagkuran. Awot pa.`
  },
]

const current = ref(0)
const activePrayer = computed(() => prayers[current.value])
const activePrayerSentences = computed(() => activePrayer.value.text
  .split(/(?<=\.)\s+|\n+/)
  .map(sentence => sentence.trim())
  .filter(Boolean))
const selectDay = (index) => { current.value = index }
const move = (amount) => { current.value = (current.value + amount + prayers.length) % prayers.length }
</script>

<template>
  <section class="novena-slider" aria-labelledby="novena-prayer-title">
    <details class="shared-prayer repentance" open>
      <summary><span>Prayer before each day</span><strong>Gawe nin Pagsolsol</strong></summary>
      <div class="shared-prayer-copy"><p>{{ gaweNinPagsolsol }}</p></div>
    </details>

    <div class="novena-days" role="tablist" aria-label="Nine days of the novena">
      <button v-for="(prayer, index) in prayers" :key="prayer.date" type="button" role="tab" :aria-selected="current === index" :class="{ active: current === index }" @click="selectDay(index)">
        <small>Day {{ index + 1 }}</small><strong>{{ prayer.date.replace('October ', 'Oct ') }}</strong>
      </button>
    </div>

    <div class="novena-slide" role="tabpanel" aria-live="polite">
      <button class="slide-arrow previous" type="button" aria-label="Previous novena day" @click="move(-1)">‹</button>
      <article>
        <div class="slide-meta"><span>Day {{ current + 1 }} of 9</span><time>{{ activePrayer.date }}</time></div>
        <h2 id="novena-prayer-title">{{ activePrayer.day }}</h2>
        <p class="prayer-instruction">Paka pangurus, pangadyion an Gawe nin Pagsolsol, dangan ining minasunod na pamibi.</p>
        <h3>Pamibi</h3>
        <div class="prayer-copy">
          <p v-for="(sentence, index) in activePrayerSentences" :key="index">{{ sentence }}</p>
        </div>
        <p class="prayer-closing">Digdi an paghagad kan kaipuhan nin lambang saro, dangan pangadyion an Pataratara. Isunod an Gozo asin an Pamibi.</p>
      </article>
      <button class="slide-arrow next" type="button" aria-label="Next novena day" @click="move(1)">›</button>
    </div>

    <div class="slider-progress"><span :style="{ width: `${((current + 1) / prayers.length) * 100}%` }"></span></div>
    <p class="slide-count">{{ String(current + 1).padStart(2, '0') }} / 09</p>

    <details class="shared-prayer gozos-prayer">
      <summary><span>Prayer after each day</span><strong>Gozos</strong></summary>
      <div class="shared-prayer-copy gozos-copy">
        <p class="gozos-opening">O Birhen na mamuraway, Reyna nin mga linalang!<br><strong>Ika samong inoomaw, Nuestra Señora del Pilar.</strong></p>
        <div v-for="(verse, index) in gozos" :key="index" class="gozos-verse">
          <p>{{ verse }}</p>
          <b>{{ gozosResponse }}</b>
        </div>
      </div>
    </details>
  </section>
</template>

<style scoped>
.novena-slider{margin-top:34px}.shared-prayer{margin-bottom:22px;border:1px solid #d8e3ed;border-radius:12px;background:#fff;box-shadow:var(--shadow);overflow:hidden}.shared-prayer summary{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;color:var(--blue);cursor:pointer;list-style:none}.shared-prayer summary::-webkit-details-marker{display:none}.shared-prayer summary::after{content:'+';margin-left:14px;font-size:22px}.shared-prayer[open] summary::after{content:'−'}.shared-prayer summary span{margin-right:auto;color:#738397;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.shared-prayer summary strong{margin-left:18px;font:700 19px 'Libre Baskerville',serif}.shared-prayer-copy{padding:4px 28px 27px;border-top:1px solid #e5edf4}.shared-prayer-copy>p{color:#263a51;font-family:Georgia,serif;font-size:16px;line-height:1.9;white-space:pre-line}.repentance{border-left:4px solid var(--gold)}.gozos-prayer{margin:28px 0 0;border-left:4px solid var(--blue)}.gozos-copy{display:grid;grid-template-columns:1fr 1fr;gap:0 34px}.gozos-copy .gozos-opening{grid-column:1/-1;text-align:center;color:var(--blue)}.gozos-verse{padding:20px 8px;border-top:1px solid #e5edf4;text-align:center}.gozos-verse p{margin:0 0 14px;color:#263a51;font-family:Georgia,serif;line-height:1.75;white-space:pre-line}.gozos-verse b{color:var(--blue);font-family:Georgia,serif;font-size:14px}.novena-days{display:grid;grid-template-columns:repeat(9,1fr);gap:8px;margin-bottom:18px}.novena-days button{padding:10px 4px;border:1px solid #d8e3ed;border-radius:7px;background:#fff;color:#52677e;cursor:pointer}.novena-days small,.novena-days strong{display:block}.novena-days small{font-size:9px;text-transform:uppercase}.novena-days strong{margin-top:4px;font-size:11px}.novena-days button.active{border-color:var(--blue);background:var(--blue);color:#fff;box-shadow:0 7px 18px rgba(6,47,120,.2)}.novena-slide{position:relative;min-height:520px;padding:54px 90px;border-top:5px solid var(--gold);border-radius:14px;background:linear-gradient(135deg,#fff,#f1f7fc);box-shadow:var(--shadow)}.novena-slide article{max-width:790px;margin:auto}.slide-meta{display:flex;justify-content:space-between;color:#88661d;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.novena-slide h2{margin:14px 0 18px;color:var(--blue);font:700 35px 'Libre Baskerville',serif}.novena-slide h3{margin:27px 0 12px;color:var(--blue);font-size:12px;text-transform:uppercase;letter-spacing:.12em}.prayer-instruction,.prayer-closing{color:#65778a;font-size:12px;font-style:italic;line-height:1.7}.prayer-copy{display:grid;grid-template-columns:minmax(0,1fr);gap:13px;color:#263a51;font-family:Georgia,serif;font-size:16px;line-height:1.9}.prayer-copy p{margin:0}.slide-arrow{position:absolute;top:50%;width:44px;height:44px;border:1px solid #cad9e7;border-radius:50%;background:#fff;color:var(--blue);font-size:31px;line-height:1;cursor:pointer;box-shadow:0 5px 14px rgba(6,47,120,.12)}.slide-arrow:hover,.slide-arrow:focus-visible{background:var(--blue);color:#fff}.previous{left:24px}.next{right:24px}.slider-progress{height:3px;margin-top:17px;background:#dce7f0}.slider-progress span{display:block;height:100%;background:var(--gold);transition:width .25s ease}.slide-count{text-align:right;color:#60758b;font-size:11px;font-weight:700}@media(max-width:720px){.shared-prayer summary{align-items:flex-start;flex-wrap:wrap}.shared-prayer summary span{width:100%;margin-bottom:5px}.shared-prayer summary strong{margin-left:0}.gozos-copy{grid-template-columns:1fr}.gozos-copy .gozos-opening{grid-column:auto}.novena-days{display:flex;overflow-x:auto;padding-bottom:8px}.novena-days button{min-width:66px}.novena-slide{padding:42px 50px}.previous{left:8px}.next{right:8px}.novena-slide h2{font-size:27px}.prayer-copy{font-size:15px}}
</style>
