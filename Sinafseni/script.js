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
        text_ar: "ما مدى شعورك بالتواصل avec الأصدقاء والعائلة؟",
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
            { text_en: "Focus & motivation", text_fr: "Concentration & Motivation", text_ar: "التركيز والتحفيiz", score: "focus" },
            { text_en: "Relationship issues", text_fr: "Problèmes relationnels", text_ar: "مشاكل العلاقات", score: "relationships" },
            { text_en: "Career & Planning", text_fr: "Carrière & Projets", text_ar: "المسار المهني والتخطيط", score: "career" }
        ]
    }
];

let currentLang = 'en';
let currentQuestionIndex = 0;
let userAnswers = [];

// DOM Elements
let progress, qTitle, qText, optionsBox, startBtn, payBtn, langToggle;

// Initialization
function init() {
    // DOM Elements (Localisés dans init pour éviter les erreurs de chargement)
    langToggle = document.getElementById('lang-toggle');
    const consentCheck = document.getElementById('consent-check');
    startBtn = document.getElementById('start-btn');
    payBtn = document.getElementById('pay-btn');
    progress = document.getElementById('progress');
    qTitle = document.getElementById('q-title');
    qText = document.getElementById('q-text');
    optionsBox = document.getElementById('options-box');

    // Détecter la langue depuis l'attribut lang de l'HTML (défini par PHP)
    const htmlLang = document.documentElement.getAttribute('lang');
    if (htmlLang && (htmlLang === 'fr' || htmlLang === 'en' || htmlLang === 'ar')) {
        currentLang = htmlLang;
    }

    updateLanguage();
    
    if (langToggle) {
        langToggle.addEventListener('click', () => {
            currentLang = currentLang === 'en' ? 'fr' : (currentLang === 'fr' ? 'ar' : 'en');
            updateLanguage();
        });
    }

    // Vérifier l'état initial de la case à cocher
    if (consentCheck) {
        startBtn.disabled = !consentCheck.checked;
        consentCheck.addEventListener('change', (e) => {
            startBtn.disabled = !e.target.checked;
        });
    }

    if (startBtn) {
        startBtn.addEventListener('click', () => {
            console.log("Starting assessment...");
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
    langToggle.innerText = currentLang === 'en' ? 'عربي' : 'English';

    // Update all i18n strings
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (translations[currentLang][key]) {
            el.innerText = translations[currentLang][key];
        }
    });

    // Update current question if active
    if (currentQuestionIndex < questions.length) {
        loadQuestion();
    }
}

function showPage(pageId) {
    const allPages = document.querySelectorAll('.page');
    allPages.forEach(p => p.classList.remove('active'));
    
    const targetPage = document.getElementById(pageId);
    if (targetPage) {
        targetPage.classList.add('active');
        window.scrollTo(0, 0);
        if (pageId === 'page-quiz') {
            loadQuestion();
        }
    } else {
        console.error("Page not found: " + pageId);
    }
}

function loadQuestion() {
    const q = questions[currentQuestionIndex];
    if (currentLang === 'en') {
        qTitle.innerText = `Question ${currentQuestionIndex + 1}`;
        qText.innerText = q.text_en;
    } else if (currentLang === 'fr') {
        qTitle.innerText = `Question ${currentQuestionIndex + 1}`;
        qText.innerText = q.text_fr || q.text_en; // Fallback to en if fr is missing
    } else {
        qTitle.innerText = `السؤال ${currentQuestionIndex + 1}`;
        qText.innerText = q.text_ar;
    }
    
    optionsBox.innerHTML = '';
    q.options.forEach((opt, idx) => {
        const btn = document.createElement('button');
        btn.className = 'option-btn';
        if (currentLang === 'en') btn.innerText = opt.text_en;
        else if (currentLang === 'fr') btn.innerText = opt.text_fr || opt.text_en;
        else btn.innerText = opt.text_ar;
        
        btn.onclick = () => selectOption(opt);
        optionsBox.appendChild(btn);
    });

    const percent = (currentQuestionIndex / questions.length) * 100;
    progress.style.width = `${percent}%`;
}

function selectOption(option) {
    userAnswers.push(option.score);
    currentQuestionIndex++;

    if (currentQuestionIndex < questions.length) {
        loadQuestion();
    } else {
        progress.style.width = `100%`;
        showPage('page-payment');
    }
}

function processPayment() {
    payBtn.disabled = true;
    payBtn.innerText = currentLang === 'en' ? "Processing..." : "جاري المعالجة...";
    
    // Mock Delay
    setTimeout(() => {
        generateResults();
        showPage('page-results');
    }, 2000);
}

function generateResults() {
    const scores = userAnswers.slice(0, 7);
    const totalScore = scores.reduce((a, b) => a + b, 0);
    const mainArea = userAnswers[7];
    
    let resultHTML = "";
    let nextSteps = "";

    const resultsPool = {
        en: {
            mild: "You seem to be managing moderate levels of stress. It's common for students to feel this way, but small changes can help.",
            moderate: "Your scores suggest you're carrying a significant mental load. It might be helpful to reach out for more support soon.",
            high: "Your responses indicate you are going through a very difficult time. We strongly recommend speaking with a professional.",
            areas: {
                anxiety: "focus on breathing techniques and mindfulness. Break large tasks into small, manageable chunks.",
                mood: "try to maintain a consistent routine and stay connected with loved ones.",
                sleep: "limit screen time before bed and try to go to sleep at the same time every night.",
                focus: "use the Pomodoro technique (25 min study, 5 min break) to combat overwhelm.",
                relationships: "consider talking to a trusted friend or a student counselor about your feelings.",
                career: "visit the university career center for guidance and planning tools."
            }
        },
    fr: {
            mild: "Vous semblez gérer des niveaux de stress modérés. Il est fréquent que les étudiants se sentent ainsi, mais de petits changements peuvent aider.",
            moderate: "Vos scores suggèrent que vous portez une charge mentale importante. Il serait utile de chercher du soutien prochainement.",
            high: "Vos réponses indiquent que vous traversez une période très difficile. Nous vous recommandons vivement de parler à un professionnel.",
            areas: {
                anxiety: "concentrez-vous sur les techniques de respiration et la pleine conscience. Divisez les tâches en petits morceaux.",
                mood: "essayez de maintenir une routine stable et restez en contact avec vos proches.",
                sleep: "limitez le temps d'écran avant de dormir et couchez-vous à la même heure chaque soir.",
                focus: "utilisez la technique Pomodoro (25 min d'étude, 5 min de pause) pour éviter d'être submergé.",
                relationships: "envisagez de parler à un ami de confiance ou à un conseiller étudiant.",
                career: "visitez le centre de carrière de l'université pour obtenir des conseils et des outils de planification."
            }
        },
        ar: {
            mild: "يبدو أنك تتعامل مع مستويات معتدلة من التوتر. من الشائع أن يشعر الطلاب بهذه الطريقة، لكن التغييرات الصغيرة يمكن أن تساعد.",
            moderate: "تشير درجاتك إلى أنك تحمل عبئاً ذهنياً كبيراً. قد يكون من المفيد الحصول على مزيد من الدعم قريباً.",
            high: "تشير إجاباتك إلى أنك تمر بوقت عصيب للغاية. نوصي بشدة بالتحدث مع متخصص.",
            areas: {
                anxiety: "ركز على تقنيات التنفس واليقظة الذهنية. قم بتقسيم المهام الكبيرة إلى أجزاء صغيرة يمكن التحكم فيها.",
                mood: "حاول الحفاظ على روتين ثابت والبقاء على تواصل مع أحبائك.",
                sleep: "قلل من وقت الشاشة قبل النوم وحاول الذهاب إلى الفراش في نفس الوقت كل ليلة.",
                focus: "استخدم تقنية البومودورو (25 دقيقة دراسة، 5 دقائق راحة) لمحاربة الشعور بالإرهاق.",
                relationships: "فكر في التحدث مع صديق تثق به أو مستشار طلابي حول مشاعرك.",
                career: "قم بزيارة مركز التوجيه الوظيفي في الجامعة للحصول على أدوات التخطيط والتوجيه."
            }
        }
    };

    let level = "mild";
    if (totalScore > 10) level = "moderate";
    if (totalScore > 18) level = "high";

    const langPool = resultsPool[currentLang];
    resultHTML = `<p>${langPool[level]}</p>`;
    
    nextSteps = langPool.areas[mainArea] || "";

    document.getElementById('result-content').innerHTML = resultHTML;
    document.getElementById('next-steps-text').innerText = nextSteps;
}

init();
