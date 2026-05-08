<?php
include_once '../../Controller/LanguageController.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?>" dir="<?= $_SESSION['lang'] == 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Psychothérapie | ASCLEPIA</title>
    
    <!-- Styles ASCLEPIA -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Styles SI-NAFSENI (Adaptés) -->
    <link rel="stylesheet" href="../../sinafseni/style.css">
    
    <style>
        /* Ajustements pour l'intégration */
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        .sinafseni-wrapper {
            padding-top: 100px; /* Espace pour la navbar fixe */
            min-height: 80vh;
        }
        .navbar {
            background: white !important;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .btn-amber {
            background-color: #f59e0b !important;
            color: white !important;
        }
        .page {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 50px;
        }
    </style>
</head>
<body>

<!-- Navbar ASCLEPIA -->
<nav class="navbar">
  <div class="container nav-container" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 20px;">
    <a href="index.php" class="logo" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: #10b981; font-weight: bold; font-size: 1.2rem;">
      <img src="../assets/image/logo.png" alt="Logo" style="height: 40px; width: auto;">
      ASCLEPIA
    </a>
    
    <div style="display: flex; align-items: center; gap: 20px;">
        <!-- Sélecteur de Langue -->
        <div class="lang-switcher" style="display: flex; align-items: center; gap: 10px; font-weight: 500; font-size: 0.9rem;">
            <a href="?lang=fr" style="color: <?= $_SESSION['lang'] == 'fr' ? '#10b981' : '#64748b' ?>; text-decoration: none;">FR</a>
            <span style="color: #e2e8f0;">|</span>
            <a href="?lang=en" style="color: <?= $_SESSION['lang'] == 'en' ? '#10b981' : '#64748b' ?>; text-decoration: none;">EN</a>
        </div>
        <a href="index.php" class="nav-link" style="text-decoration: none; color: #64748b; font-size: 0.9rem;"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
  </div>
</nav>

<div class="sinafseni-wrapper">
    <main>
        <!-- Page 1: Landing -->
        <section id="page-landing" class="page active">
            <div class="container hero">
                <h1 data-i18n="hero-title">Find balance, feel better</h1>
                <p class="subtitle" data-i18n="hero-subtitle">سريع، آمن، ومباشr</p>
                <p data-i18n="hero-desc">Bilan confidentiel, 8 questions, 10 DT pour des solutions concrètes.</p>
                
                <div class="features">
                    <div class="feature-card">
                        <span class="icon">⚡</span>
                        <h3 data-i18n="feat-1-title">Quick Assessment</h3>
                        <p data-i18n="feat-1-desc">Takes only 1-3 minutes</p>
                    </div>
                    <div class="feature-card">
                        <span class="icon">🔒</span>
                        <h3 data-i18n="feat-2-title">Confidential</h3>
                        <p data-i18n="feat-2-desc">No data stored on server</p>
                    </div>
                    <div class="feature-card">
                        <span class="icon">🛠️</span>
                        <h3 data-i18n="feat-3-title">Actionable</h3>
                        <p data-i18n="feat-3-desc">Solutions tailored for you</p>
                    </div>
                </div>

                <div class="consent-box" style="margin: 0 auto; max-width: 600px;">
                    <label class="checkbox-container">
                        <input type="checkbox" id="consent-check">
                        <span class="checkmark"></span>
                        <span data-i18n="consent-text">Je confirme être étudiant universitaire et j'accepte la politique de confidentialité</span>
                    </label>
                    <p class="privacy-note" data-i18n="privacy-note">Note: This is a client-side tool. Your data is never saved or shared.</p>
                </div>

                <button id="start-btn" class="btn-primary" disabled data-i18n="start-btn" style="max-width: 400px; margin: 20px auto; display: block;">Start Assessment — ابدأ التقييم</button>
                
                <div class="how-it-works" id="how-it-works">
                    <h2 data-i18n="how-title">How it works</h2>
                    <div class="steps">
                        <div class="step"><span>1</span> <p data-i18n="step-1">Accept Privacy Policy</p></div>
                        <div class="step-arrow">→</div>
                        <div class="step"><span>2</span> <p data-i18n="step-2">Answer 8 Questions</p></div>
                        <div class="step-arrow">→</div>
                        <div class="step"><span>3</span> <p data-i18n="step-3">Pay 10 DT</p></div>
                        <div class="step-arrow">→</div>
                        <div class="step"><span>4</span> <p data-i18n="step-4">See Solutions</p></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Page 3: Questionnaire -->
        <section id="page-quiz" class="page">
            <div class="container quiz-container">
                <div class="progress-bar">
                    <div id="progress" style="width: 0%;"></div>
                </div>
                <div id="question-box">
                    <h2 id="q-title">Question 1</h2>
                    <p id="q-text"></p>
                    <div id="options-box" class="options-grid"></div>
                </div>
            </div>
        </section>

        <!-- Page 4: Payment -->
        <section id="page-payment" class="page">
            <div class="container text-center">
                <h2 data-i18n="pay-title">Final Step: Secure Payment</h2>
                <p data-i18n="pay-desc">Pay 10 DT to access your personalized psychological report and solutions.</p>
                <div class="payment-mock">
                    <div class="card-demo">
                        <p style="font-size: 2rem;">10 Dinars</p>
                        <hr>
                        <p>si-nafseni assessment</p>
                    </div>
                    <button id="pay-btn" class="btn-primary btn-amber" data-i18n="pay-btn" style="max-width: 400px; margin: 0 auto;">Pay 10 Dinars — ادفع 10 دنانير</button>
                </div>
                <p class="disclaimer" data-i18n="medical-disclaimer">Disclaimer: This tool is not a substitute for professional diagnosis.</p>
            </div>
        </section>

        <!-- Page 5: Results -->
        <section id="page-results" class="page">
            <div class="container">
                <div class="result-header">
                    <h2 data-i18n="results-title">Your Personalized Assessment</h2>
                    <p data-i18n="results-subtitle">Based on your responses, here are some insights and concrete steps.</p>
                </div>
                <div id="result-content" class="result-card">
                    <!-- Dynamic Content -->
                </div>
                <div class="emergency-box">
                    <h3 data-i18n="next-steps-title">Next Steps</h3>
                    <p id="next-steps-text"></p>
                    <hr>
                    <p class="emergency-info" data-i18n="emergency-info">If you are in crisis, please call emergency services immediately.</p>
                </div>
                <button onclick="location.reload()" class="btn-secondary" data-i18n="restart-btn" style="margin-bottom: 30px;">Restart (Reset all data)</button>
            </div>
        </section>
    </main>
</div>

<!-- Bouton Langue SI-NAFSENI caché (on peut l'utiliser pour forcer la langue si besoin) -->
<button id="lang-toggle" style="display: none;"></button>

<footer class="footer" style="background: #1e293b; color: white; padding: 40px 0; text-align: center;">
    <div class="container">
        <p>&copy; 2026 ASCLEPIA x SI-NAFSENI. Tous droits réservés.</p>
    </div>
</footer>

<!-- Scripts -->
<script>
    // Configuration de la langue
    const phpLang = "<?= $_SESSION['lang'] ?>";
    let currentLang = phpLang === 'fr' ? 'fr' : (phpLang === 'ar' ? 'ar' : 'en');

    const translations = {
        en: {
            "hero-title": "Find balance, feel better",
            "hero-subtitle": "Quick, Secure, Direct",
            "hero-desc": "Confidential assessment, 8 questions, 10 DT for concrete solutions.",
            "feat-1-title": "Quick Assessment",
            "feat-1-desc": "Takes only 1-3 minutes",
            "feat-2-title": "Confidential",
            "feat-2-desc": "No data stored on server",
            "feat-3-title": "Actionable",
            "feat-3-desc": "Solutions tailored for you",
            "consent-text": "I confirm I am a university student and I accept the privacy policy",
            "privacy-note": "Note: This is a client-side tool. Your data is never saved or shared.",
            "start-btn": "Start Assessment",
            "how-title": "How it works",
            "step-1": "Accept Privacy Policy",
            "step-2": "Answer 8 Questions",
            "step-3": "Pay 10 DT",
            "step-4": "See Solutions",
            "emergency-link": "In case of emergency, contact your university counseling center or local emergency services.",
            "pay-title": "Final Step: Secure Payment",
            "pay-desc": "Pay 10 DT to access your personalized psychological report and solutions.",
            "pay-btn": "Pay 10 Dinars",
            "medical-disclaimer": "Disclaimer: This tool is not a substitute for professional diagnosis. If symptoms are severe, seek emergency help.",
            "results-title": "Your Personalized Assessment",
            "results-subtitle": "Based on your responses, here are some insights and concrete steps.",
            "next-steps-title": "Next Steps",
            "emergency-info": "If you are in crisis, please call emergency services immediately.",
            "restart-btn": "Restart (Reset all data)",
            "privacy-link": "Privacy Policy"
        },
        fr: {
            "hero-title": "Trouvez votre équilibre",
            "hero-subtitle": "Rapide, Sécurisé, Direct",
            "hero-desc": "Bilan confidentiel, 8 questions, 10 DT pour des solutions concrètes.",
            "feat-1-title": "Évaluation Rapide",
            "feat-1-desc": "Prend seulement 1 à 3 minutes",
            "feat-2-title": "Confidentiel",
            "feat-2-desc": "Aucune donnée stockée sur le serveur",
            "feat-3-title": "Pratique",
            "feat-3-desc": "Solutions adaptées à vos besoins",
            "consent-text": "Je confirme être étudiant et j'accepte la politique de confidentialité",
            "privacy-note": "Note : Cet outil fonctionne localement. Vos données ne sont jamais sauvegardées.",
            "start-btn": "Démarrer l'évaluation",
            "how-title": "Comment ça marche",
            "step-1": "Accepter la politique",
            "step-2": "Répondre à 8 questions",
            "step-3": "Payer 10 DT",
            "step-4": "Voir les solutions",
            "emergency-link": "En cas d'urgence, contactez votre centre de conseil universitaire ou les secours.",
            "pay-title": "Dernière étape : Paiement Sécurisé",
            "pay-desc": "Payez 10 DT pour accéder à votre rapport personnalisé et vos solutions.",
            "pay-btn": "Payer 10 Dinars",
            "medical-disclaimer": "Avertissement : Cet outil ne remplace pas un diagnostic professionnel.",
            "results-title": "Votre Bilan Personnalisé",
            "results-subtitle": "Selon vos réponses, voici quelques pistes et étapes concrètes.",
            "next-steps-title": "Prochaines étapes",
            "emergency-info": "Si vous êtes en crise, appelez immédiatement les services d'urgence.",
            "restart-btn": "Recommencer (Effacer les données)",
            "privacy-link": "Politique de confidentialité"
        },
        ar: {
            "hero-title": "ابحث عن توازنك، اشعر بتحسن",
            "hero-subtitle": "سريع، آمن، ومباشر",
            "hero-desc": "تقييم سري، 8 أسئلة، 10 دنيار لحلول ملموسة.",
            "feat-1-title": "تقييم سريع",
            "feat-1-desc": "يستغرق من 1 إلى 3 دقائق فقط",
            "feat-2-title": "سري للغاية",
            "feat-2-desc": "لا يتم تخزين أي بيانات على الخادم",
            "feat-3-title": "حلول عملية",
            "feat-3-desc": "حلول مصممة خصيصاً لك",
            "consent-text": "أؤكد أنني طالب جامعي وأوافق على سياسة الخصوصية",
            "privacy-note": "ملاحظة: هذه الأداة تعمل من جانب العميل فقط. لا يتم حفظ بياناتك أو مشاركتها أبداً.",
            "start-btn": "ابدأ التقييم",
            "how-title": "كيف يعمل الموقع",
            "step-1": "قبول سياسة الخصوصية",
            "step-2": "الإجابة على 8 أسئلة",
            "step-3": "دفع 10 دنيار",
            "step-4": "مشاهدة الحلول",
            "emergency-link": "في حالات الطوارئ، اتصل بمركز الاستشارة الجامعي الخاص بك أو بخدمات الطوارئ المحلية.",
            "pay-title": "الخطوة الأخيرة: دفع آمن",
            "pay-desc": "ادفع 10 دنيار للحصول على تقريرك النفسي المخصص والحلول.",
            "pay-btn": "ادفع 10 دنانير",
            "medical-disclaimer": "إخلاء مسؤولية: هذه الأداة ليست بديلاً عن التشخيص المهني. إذا كانت الأعراض شديدة، اطلب المساعدة الطارئة.",
            "results-title": "تقييمك الشخصي",
            "results-subtitle": "بناءً على إجاباتك، إليك بعض الرؤى والخطوات الملموسة.",
            "next-steps-title": "الخطوات القادمة",
            "emergency-info": "إذا كنت في أزمة، يرجى الاتصال بخدمات الطوارئ فوراً.",
            "restart-btn": "إعادة البدء (مسح جميع البيانات)",
            "privacy-link": "سياسة الخصوصية"
        }
    };

    const questions = [
        {
            id: "q1",
            text_en: "Over the past two weeks, how often have you felt nervous, anxious, or on edge?",
            text_fr: "Au cours des deux dernières semaines, à quelle fréquence vous êtes-vous senti nerveux, anxieux ou à cran ?",
            text_ar: "خلال الأسبوعين الماضيين، كم مرة شعرت بالتوتر أو القلق أو الانزعاج الشديد؟",
            options: [
                { text_en: "Not at all", text_fr: "Pas du tout", text_ar: "ليس على الإطلاق", score: 0 },
                { text_en: "Several days", text_fr: "Plusieurs jours", text_ar: "عدة أيام", score: 1 },
                { text_en: "More than half the days", text_fr: "Plus de la moitié du temps", text_ar: "أكثر من نصف الأيام", score: 2 },
                { text_en: "Nearly every day", text_fr: "Presque tous les jours", text_ar: "كل يوم تقريبًا", score: 3 }
            ]
        },
        {
            id: "q2",
            text_en: "Over the past two weeks, how often have you had little interest or pleasure in doing things?",
            text_fr: "Au cours des deux dernières semaines, à quelle fréquence avez-vous eu peu d'intérêt ou de plaisir à faire des choses ?",
            text_ar: "خلال الأسبوعين الماضيين، كم مرة شعرت بضعف الاهتمام أو المتعة في القيام بالأشياء؟",
            options: [
                { text_en: "Not at all", text_fr: "Pas du tout", text_ar: "ليس على الإطلاق", score: 0 },
                { text_en: "Several days", text_fr: "Plusieurs jours", text_ar: "عدة أيام", score: 1 },
                { text_en: "More than half the days", text_fr: "Plus de la moitié du temps", text_ar: "أكثر من نصف الأيام", score: 2 },
                { text_en: "Nearly every day", text_fr: "Presque tous les jours", text_ar: "كل يوم تقريبًا", score: 3 }
            ]
        },
        {
            id: "q3",
            text_en: "How often do you feel overwhelmed by academic pressure?",
            text_fr: "À quelle fréquence vous sentez-vous submergé par la pression académique ?",
            text_ar: "كم مرة تشعر بالإرهاق بسبب الضغوط الأكاديمية؟",
            options: [
                { text_en: "Never", text_fr: "Jamais", text_ar: "أبداً", score: 0 },
                { text_en: "Sometimes", text_fr: "Parfois", text_ar: "أحياناً", score: 1 },
                { text_en: "Often", text_fr: "Souvent", text_ar: "غالباً", score: 2 },
                { text_en: "Always", text_fr: "Toujours", text_ar: "دائماً", score: 3 }
            ]
        },
        {
            id: "q4",
            text_en: "How well are you sleeping recently?",
            text_fr: "Comment dormez-vous ces derniers temps ?",
            text_ar: "كيف هي جودة نومك مؤخراً؟",
            options: [
                { text_en: "Very well", text_fr: "Très bien", text_ar: "جيد جداً", score: 0 },
                { text_en: "Okay", text_fr: "Correctement", text_ar: "مقبول", score: 1 },
                { text_en: "Poor", text_fr: "Mal", text_ar: "سيء", score: 2 },
                { text_en: "Very poor", text_fr: "Très mal", text_ar: "سيء جداً", score: 3 }
            ]
        },
        {
            id: "q5",
            text_en: "Do you have difficulty concentrating on studying or lectures?",
            text_fr: "Avez-vous des difficultés à vous concentrer sur vos études ou vos cours ?",
            text_ar: "هل تجد صعوبة في التركيز على الدراسة أو المحاضرات؟",
            options: [
                { text_en: "Not at all", text_fr: "Pas du tout", text_ar: "ليس على الإطلاق", score: 0 },
                { text_en: "A little", text_fr: "Un peu", text_ar: "قليلاً", score: 1 },
                { text_en: "Moderately", text_fr: "Moyennement", text_ar: "بشكل متوسط", score: 2 },
                { text_en: "A lot", text_fr: "Beaucoup", text_ar: "كثيراً", score: 3 }
            ]
        },
        {
            id: "q6",
            text_en: "Have you had thoughts that life is not worth living or self-harm?",
            text_fr: "Avez-vous des pensées selon lesquelles la vie ne vaut pas la peine d'être vécue ou d'auto-mutilation ?",
            text_ar: "هل راودتك أفكار بأن الحياة لا تستحق العيش أو أفكار لإيذاء النفس؟",
            options: [
                { text_en: "Never", text_fr: "Jamais", text_ar: "أبداً", score: 0 },
                { text_en: "Rarely", text_fr: "Rarement", text_ar: "نادراً", score: 5 },
                { text_en: "Sometimes", text_fr: "Parfois", text_ar: "أحياناً", score: 10 },
                { text_en: "Often", text_fr: "Souvent", text_ar: "غالباً", score: 15 }
            ]
        },
        {
            id: "q7",
            text_en: "How connected do you feel to friends or family?",
            text_fr: "À quel point vous sentez-vous proche de vos amis ou de votre famille ?",
            text_ar: "ما مدى شعورك بالتواصل مع الأصدقاء والعائلة؟",
            options: [
                { text_en: "Very connected", text_fr: "Très proche", text_ar: "متواصل جداً", score: 0 },
                { text_en: "Somewhat", text_fr: "Assez proche", text_ar: "إلى حد ما", score: 1 },
                { text_en: "A little", text_fr: "Un peu", text_ar: "قليلاً", score: 2 },
                { text_en: "Disconnected", text_fr: "Isolé", text_ar: "منفصل", score: 3 }
            ]
        },
        {
            id: "q8",
            text_en: "Which area do you most want help with?",
            text_fr: "Dans quel domaine souhaitez-vous le plus d'aide ?",
            text_ar: "ما هو المجال الذي تريد المساعدة فيه أكثر من غيره؟",
            options: [
                { text_en: "Stress & Anxiety", text_fr: "Stress & Anxiété", text_ar: "التوتر والقلق", score: "anxiety" },
                { text_en: "Mood / Depression", text_fr: "Humeur / Dépression", text_ar: "المزاج والاكتئاب", score: "mood" },
                { text_en: "Sleep problems", text_fr: "Problèmes de sommeil", text_ar: "مشاكل النوم", score: "sleep" },
                { text_en: "Focus & motivation", text_fr: "Concentration & Motivation", text_ar: "التركيز والتحفيز", score: "focus" },
                { text_en: "Relationship issues", text_fr: "Problèmes relationnels", text_ar: "مشاكل العلاقات", score: "relationships" },
                { text_en: "Career & Planning", text_fr: "Carrière & Projets", text_ar: "المسار المهني والتخطيط", score: "career" }
            ]
        }
    ];

    let currentQuestionIndex = 0;
    let userAnswers = [];
    let progress, qTitle, qText, optionsBox, startBtn, payBtn;

    function init() {
        startBtn = document.getElementById('start-btn');
        payBtn = document.getElementById('pay-btn');
        progress = document.getElementById('progress');
        qTitle = document.getElementById('q-title');
        qText = document.getElementById('q-text');
        optionsBox = document.getElementById('options-box');
        const consentCheck = document.getElementById('consent-check');

        updateLanguage();

        if (consentCheck) {
            startBtn.disabled = !consentCheck.checked;
            consentCheck.addEventListener('change', (e) => {
                startBtn.disabled = !e.target.checked;
            });
        }

        if (startBtn) {
            startBtn.addEventListener('click', () => {
                showPage('page-quiz');
            });
        }

        if (payBtn) {
            payBtn.addEventListener('click', processPayment);
        }
    }

    function updateLanguage() {
        const dir = currentLang === 'ar' ? 'rtl' : 'ltr';
        document.documentElement.setAttribute('dir', dir);
        document.documentElement.setAttribute('lang', currentLang);

        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (translations[currentLang] && translations[currentLang][key]) {
                el.innerText = translations[currentLang][key];
            }
        });

        if (currentQuestionIndex < questions.length && document.getElementById('page-quiz').classList.contains('active')) {
            loadQuestion();
        }
    }

    function showPage(pageId) {
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        const target = document.getElementById(pageId);
        if (target) {
            target.classList.add('active');
            window.scrollTo(0, 0);
            if (pageId === 'page-quiz') loadQuestion();
        }
    }

    function loadQuestion() {
        const q = questions[currentQuestionIndex];
        const qNum = currentQuestionIndex + 1;
        
        if (currentLang === 'ar') {
            qTitle.innerText = "السؤال " + qNum;
            qText.innerText = q.text_ar;
        } else if (currentLang === 'fr') {
            qTitle.innerText = "Question " + qNum;
            qText.innerText = q.text_fr;
        } else {
            qTitle.innerText = "Question " + qNum;
            qText.innerText = q.text_en;
        }

        optionsBox.innerHTML = '';
        q.options.forEach(opt => {
            const btn = document.createElement('button');
            btn.className = 'option-btn';
            btn.innerText = opt['text_' + currentLang] || opt.text_en;
            btn.onclick = () => selectOption(opt);
            optionsBox.appendChild(btn);
        });

        progress.style.width = ((currentQuestionIndex / questions.length) * 100) + '%';
    }

    function selectOption(option) {
        userAnswers.push(option.score);
        currentQuestionIndex++;
        if (currentQuestionIndex < questions.length) {
            loadQuestion();
        } else {
            progress.style.width = '100%';
            showPage('page-payment');
        }
    }

    function processPayment() {
        payBtn.disabled = true;
        payBtn.innerText = currentLang === 'ar' ? "جاري المعالجة..." : (currentLang === 'fr' ? "Traitement..." : "Processing...");
        setTimeout(() => {
            generateResults();
            showPage('page-results');
        }, 2000);
    }

    function generateResults() {
        const scores = userAnswers.slice(0, 7);
        const totalScore = scores.reduce((a, b) => (typeof a === 'number' ? a : 0) + (typeof b === 'number' ? b : 0), 0);
        const mainArea = userAnswers[7];

        const resultsPool = {
            en: {
                mild: "You seem to be managing moderate levels of stress.",
                moderate: "Your scores suggest you're carrying a significant mental load.",
                high: "Your responses indicate you are going through a very difficult time.",
                areas: {
                    anxiety: "focus on breathing techniques.",
                    mood: "stay connected with loved ones.",
                    sleep: "limit screen time before bed.",
                    focus: "use the Pomodoro technique.",
                    relationships: "talk to a trusted friend.",
                    career: "visit the university career center."
                }
            },
            fr: {
                mild: "Vous semblez gérer des niveaux de stress modérés.",
                moderate: "Vos scores suggèrent que vous portez une charge mentale importante.",
                high: "Vos réponses indiquent que vous traversez une période très difficile.",
                areas: {
                    anxiety: "concentrez-vous sur la respiration.",
                    mood: "restez en contact avec vos proches.",
                    sleep: "limitez les écrans le soir.",
                    focus: "utilisez la méthode Pomodoro.",
                    relationships: "parlez à un ami de confiance.",
                    career: "visitez le centre de carrière."
                }
            },
            ar: {
                mild: "يبدو أنك تتعامل مع مستويات معتدلة من التوتر.",
                moderate: "تشير درجاتك إلى أنك تحمل عبئاً ذهنياً كبيراً.",
                high: "تشير إجاباتك إلى أنك تمر بوقت عصيب للغاية.",
                areas: {
                    anxiety: "ركز على تقنيات التنفس.",
                    mood: "ابق على تواصل مع أحبائك.",
                    sleep: "قلل من وقت الشاشة قبل النوم.",
                    focus: "استخدم تقنية البومودورو.",
                    relationships: "تحدث مع صديق تثق به.",
                    career: "قم بزيارة مركز التوجيه الوظيفي."
                }
            }
        };

        let level = totalScore > 18 ? "high" : (totalScore > 10 ? "moderate" : "mild");
        const pool = resultsPool[currentLang] || resultsPool.en;
        
        document.getElementById('result-content').innerHTML = `<p>${pool[level]}</p>`;
        document.getElementById('next-steps-text').innerText = pool.areas[mainArea] || "";
    }

    // Lancer l'initialisation au chargement du DOM
    document.addEventListener('DOMContentLoaded', init);
</script>

</body>
</html>
