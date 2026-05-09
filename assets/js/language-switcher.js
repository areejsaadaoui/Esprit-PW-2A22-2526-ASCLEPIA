const LanguageSwitcher = (() => {
    const translations = {
        fr: {
            'Dashboard': 'Dashboard',
            'Consultations': 'Consultations',
            'Ajouter': 'Ajouter',
            'Ordonnances': 'Ordonnances',
            'Nouvelle consultation': 'Nouvelle consultation',
            'Nouvelle ordonnance': 'Nouvelle ordonnance',
            'Voir tout': 'Voir tout',
            'Total consultations': 'Total consultations',
            'Planifiées': 'Planifiées',
            'Terminées': 'Terminées',
            'Annulées': 'Annulées',
            'Prochaines consultations': 'Prochaines consultations',
            'Dernières consultations terminées': 'Dernières consultations terminées',
            'Aucune consultation planifiée': 'Aucune consultation planifiée',
            'Aucune consultation terminée': 'Aucune consultation terminée',
            'Aucune consultation trouvée': 'Aucune consultation trouvée',
            'Aucune ordonnance': 'Aucune ordonnance',
            'Aucune ordonnance trouvée': 'Aucune ordonnance trouvée',
            'Ordonnance': 'Ordonnance',
            'Ajouter une ordonnance': 'Ajouter une ordonnance',
            'Ajouter une consultation': 'Ajouter une consultation',
            'Consultation * (consultations terminées uniquement)': 'Consultation * (consultations terminées uniquement)',
            'Signature du médecin *': 'Signature du médecin *',
            'Dessinez votre signature dans le cadre ci-dessous': 'Dessinez votre signature dans le cadre ci-dessous',
            'La signature est obligatoire.': 'La signature est obligatoire.',
            'Annuler': 'Annuler',
            'Modifier': 'Modifier',
            'Supprimer': 'Supprimer',
            'Aucune consultation': 'Aucune consultation',
            'Commencez par ajouter une consultation.': 'Commencez par ajouter une consultation.',
            'Liste des consultations': 'Liste des consultations',
            'Mes Consultations': 'Mes Consultations',
            'Mes Ordonnances': 'Mes Ordonnances',
            'Accueil': 'Accueil',
            'Pharmacies': 'Pharmacies',
            'Contact': 'Contact',
            'Connexion': 'Connexion',
            "S'inscrire": "S'inscrire",
            'Consultez l\'historique de vos consultations médicales.': 'Consultez l\'historique de vos consultations médicales.',
            'Consultez vos ordonnances et traitements prescrits.': 'Consultez vos ordonnances et traitements prescrits.',
            'Consultation': 'Consultation',
            'Médicaments': 'Médicaments',
            'Notes': 'Notes',
            'Diagnostique': 'Diagnostique',
            'Durée du traitement': 'Durée du traitement',
            'N°': 'N°',
            'Signature :': 'Signature :',
            'Scanner pour verifier': 'Scanner pour verifier',
            'Ordonnances - ASCLEPIA Admin': 'Ordonnances - ASCLEPIA Admin',
            'Ajouter Ordonnance - ASCLEPIA Admin': 'Ajouter Ordonnance - ASCLEPIA Admin',
            'Modifier Ordonnance - ASCLEPIA Admin': 'Modifier Ordonnance - ASCLEPIA Admin',
            'Supprimer Consultation - ASCLEPIA Admin': 'Supprimer Consultation - ASCLEPIA Admin',
            'Supprimer Ordonnance - ASCLEPIA Admin': 'Supprimer Ordonnance - ASCLEPIA Admin',
            'Ajouter Consultation - ASCLEPIA Admin': 'Ajouter Consultation - ASCLEPIA Admin',
            'Modifier Consultation - ASCLEPIA Admin': 'Modifier Consultation - ASCLEPIA Admin',
            'Dashboard - ASCLEPIA Admin': 'Dashboard - ASCLEPIA Admin',
            'Mes Ordonnances - ASCLEPIA': 'Mes Ordonnances - ASCLEPIA',
            'Mes Consultations - ASCLEPIA': 'Mes Consultations - ASCLEPIA',
            'Lang:': 'Lang:'
        },
        en: {
            'Dashboard': 'Dashboard',
            'Consultations': 'Consultations',
            'Ajouter': 'Add',
            'Ordonnances': 'Prescriptions',
            'Nouvelle consultation': 'New consultation',
            'Nouvelle ordonnance': 'New prescription',
            'Voir tout': 'View all',
            'Total consultations': 'Total consultations',
            'Planifiées': 'Scheduled',
            'Terminées': 'Completed',
            'Annulées': 'Canceled',
            'Prochaines consultations': 'Upcoming consultations',
            'Dernières consultations terminées': 'Latest completed consultations',
            'Aucune consultation planifiée': 'No scheduled consultations',
            'Aucune consultation terminée': 'No completed consultations',
            'Aucune consultation trouvée': 'No consultation found',
            'Aucune ordonnance': 'No prescription',
            'Aucune ordonnance trouvée': 'No prescription found',
            'Ordonnance': 'Prescription',
            'Ajouter une ordonnance': 'Add a prescription',
            'Ajouter une consultation': 'Add a consultation',
            'Consultation * (consultations terminées uniquement)': 'Consultation * (completed consultations only)',
            'Signature du médecin *': 'Doctor signature *',
            'Dessinez votre signature dans le cadre ci-dessous': 'Draw your signature in the box below',
            'La signature est obligatoire.': 'Signature is required.',
            'Annuler': 'Cancel',
            'Modifier': 'Edit',
            'Supprimer': 'Delete',
            'Aucune consultation': 'No consultation',
            'Commencez par ajouter une consultation.': 'Start by adding a consultation.',
            'Liste des consultations': 'Consultation list',
            'Mes Consultations': 'My consultations',
            'Mes Ordonnances': 'My prescriptions',
            'Accueil': 'Home',
            'Pharmacies': 'Pharmacies',
            'Contact': 'Contact',
            'Connexion': 'Login',
            "S'inscrire": 'Sign up',
            'Consultez l\'historique de vos consultations médicales.': 'View your medical consultation history.',
            'Consultez vos ordonnances et traitements prescrits.': 'View your prescriptions and treatments.',
            'Consultation': 'Consultation',
            'Médicaments': 'Medications',
            'Notes': 'Notes',
            'Diagnostique': 'Diagnosis',
            'Durée du traitement': 'Treatment duration',
            'N°': 'No.',
            'Signature :': 'Signature :',
            'Scanner pour verifier': 'Scan to verify',
            'Ordonnances - ASCLEPIA Admin': 'Prescriptions - ASCLEPIA Admin',
            'Ajouter Ordonnance - ASCLEPIA Admin': 'Add Prescription - ASCLEPIA Admin',
            'Modifier Ordonnance - ASCLEPIA Admin': 'Edit Prescription - ASCLEPIA Admin',
            'Supprimer Consultation - ASCLEPIA Admin': 'Delete Consultation - ASCLEPIA Admin',
            'Supprimer Ordonnance - ASCLEPIA Admin': 'Delete Prescription - ASCLEPIA Admin',
            'Ajouter Consultation - ASCLEPIA Admin': 'Add Consultation - ASCLEPIA Admin',
            'Modifier Consultation - ASCLEPIA Admin': 'Edit Consultation - ASCLEPIA Admin',
            'Dashboard - ASCLEPIA Admin': 'Dashboard - ASCLEPIA Admin',
            'Mes Ordonnances - ASCLEPIA': 'My Prescriptions - ASCLEPIA',
            'Mes Consultations - ASCLEPIA': 'My Consultations - ASCLEPIA',
            'Lang:': 'Lang:'
        },
        ar: {
            'Dashboard': 'لوحة القيادة',
            'Consultations': 'الاستشارات',
            'Ajouter': 'إضافة',
            'Ordonnances': 'الوصفات الطبية',
            'Nouvelle consultation': 'استشارة جديدة',
            'Nouvelle ordonnance': 'وصفة جديدة',
            'Voir tout': 'عرض الكل',
            'Total consultations': 'إجمالي الاستشارات',
            'Planifiées': 'مجدولة',
            'Terminées': 'مكتملة',
            'Annulées': 'ملغاة',
            'Prochaines consultations': 'الاستشارات القادمة',
            'Dernières consultations terminées': 'آخر الاستشارات المكتملة',
            'Aucune consultation planifiée': 'لا توجد استشارات مجدولة',
            'Aucune consultation terminée': 'لا توجد استشارات مكتملة',
            'Aucune consultation trouvée': 'لم يتم العثور على استشارة',
            'Aucune ordonnance': 'لا توجد وصفة طبية',
            'Aucune ordonnance trouvée': 'لم يتم العثور على وصفة طبية',
            'Ordonnance': 'الوصفة الطبية',
            'Ajouter une ordonnance': 'إضافة وصفة طبية',
            'Ajouter une consultation': 'إضافة استشارة',
            'Consultation * (consultations terminées uniquement)': 'الاستشارة * (الاستشارات المكتملة فقط)',
            'Signature du médecin *': 'توقيع الطبيب *',
            'Dessinez votre signature dans le cadre ci-dessous': 'ارسم توقيعك داخل المربع أدناه',
            'La signature est obligatoire.': 'التوقيع مطلوب.',
            'Annuler': 'إلغاء',
            'Modifier': 'تعديل',
            'Supprimer': 'حذف',
            'Aucune consultation': 'لا توجد استشارة',
            'Commencez par ajouter une consultation.': 'ابدأ بإضافة استشارة.',
            'Liste des consultations': 'قائمة الاستشارات',
            'Mes Consultations': 'استشاراتي',
            'Mes Ordonnances': 'وصفاتي الطبية',
            'Accueil': 'الرئيسية',
            'Pharmacies': 'الصيدليات',
            'Contact': 'اتصال',
            'Connexion': 'تسجيل الدخول',
            "S'inscrire": 'اشترك',
            'Consultez l\'historique de vos consultations médicales.': 'عرض سجل الاستشارات الطبية الخاصة بك.',
            'Consultez vos ordonnances et traitements prescrits.': 'عرض وصفاتك الطبية والعلاجات الموصوفة.',
            'Consultation': 'استشارة',
            'Médicaments': 'الأدوية',
            'Notes': 'ملاحظات',
            'Diagnostique': 'تشخيص',
            'Durée du traitement': 'مدة العلاج',
            'N°': 'رقم',
            'Signature :': 'التوقيع :',
            'Scanner pour verifier': 'امسح للتحقق',
            'Ordonnances - ASCLEPIA Admin': 'الوصفات الطبية - ASCLEPIA Admin',
            'Ajouter Ordonnance - ASCLEPIA Admin': 'إضافة وصفة طبية - ASCLEPIA Admin',
            'Modifier Ordonnance - ASCLEPIA Admin': 'تعديل وصفة طبية - ASCLEPIA Admin',
            'Supprimer Consultation - ASCLEPIA Admin': 'حذف استشارة - ASCLEPIA Admin',
            'Supprimer Ordonnance - ASCLEPIA Admin': 'حذف وصفة طبية - ASCLEPIA Admin',
            'Ajouter Consultation - ASCLEPIA Admin': 'إضافة استشارة - ASCLEPIA Admin',
            'Modifier Consultation - ASCLEPIA Admin': 'تعديل استشارة - ASCLEPIA Admin',
            'Dashboard - ASCLEPIA Admin': 'لوحة القيادة - ASCLEPIA Admin',
            'Mes Ordonnances - ASCLEPIA': 'وصفاتي الطبية - ASCLEPIA',
            'Mes Consultations - ASCLEPIA': 'استشاراتي - ASCLEPIA',
            'Lang:': 'اللغة:'
        }
    };

    const placeholders = {
        fr: {
            'Ex: Paracétamol 500mg, Ibuprofène 400mg...': 'Ex: Paracétamol 500mg, Ibuprofène 400mg...',
            'Ex: Prendre 1 comprimé 3 fois par jour après les repas...': 'Ex: Prendre 1 comprimé 3 fois par jour après les repas...',
            'Ex: 7': 'Ex: 7',
            '-- Choisir une consultation --': '-- Choisir une consultation --'
        },
        en: {
            'Ex: Paracétamol 500mg, Ibuprofène 400mg...': 'Ex: Paracetamol 500mg, Ibuprofen 400mg...',
            'Ex: Prendre 1 comprimé 3 fois par jour après les repas...': 'Ex: Take 1 tablet 3 times a day after meals...',
            'Ex: 7': 'Ex: 7',
            '-- Choisir une consultation --': '-- Choose a consultation --'
        },
        ar: {
            'Ex: Paracétamol 500mg, Ibuprofène 400mg...': 'مثال: باراسيتامول 500 مجم، إيبوبرفين 400 مجم...',
            'Ex: Prendre 1 comprimé 3 fois par jour après les repas...': 'مثال: تناول قرص واحد 3 مرات في اليوم بعد الوجبات...',
            'Ex: 7': 'مثال: 7',
            '-- Choisir une consultation --': '-- اختر استشارة --'
        }
    };

    const supportedLanguages = ['fr', 'en', 'ar'];
    const defaultLang = 'fr';

    function getSavedLanguage() {
        const stored = localStorage.getItem('asclepia_lang');
        if (supportedLanguages.includes(stored)) {
            return stored;
        }
        return defaultLang;
    }

    function saveLanguage(lang) {
        localStorage.setItem('asclepia_lang', lang);
    }

    function setDirection(lang) {
        const dir = lang === 'ar' ? 'rtl' : 'ltr';
        document.documentElement.lang = lang;
        document.documentElement.dir = dir;
        document.body.style.direction = dir;
        if (dir === 'rtl') {
            document.body.classList.add('rtl');
        } else {
            document.body.classList.remove('rtl');
        }
    }

    function translateTextNodes(node, map) {
        const walker = document.createTreeWalker(node, NodeFilter.SHOW_TEXT, {
            acceptNode(textNode) {
                if (!textNode.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
                const text = textNode.nodeValue.trim();
                if (map[text] !== undefined) return NodeFilter.FILTER_ACCEPT;
                return NodeFilter.FILTER_REJECT;
            }
        });

        const nodes = [];
        while (walker.nextNode()) {
            nodes.push(walker.currentNode);
        }
        nodes.forEach(textNode => {
            const text = textNode.nodeValue.trim();
            const translation = map[text];
            if (translation !== undefined) {
                textNode.nodeValue = textNode.nodeValue.replace(text, translation);
            }
        });
    }

    function translatePlaceholders(lang) {
        const map = placeholders[lang] || placeholders[defaultLang];
        document.querySelectorAll('input[placeholder], textarea[placeholder], select').forEach(el => {
            const placeholder = el.getAttribute('placeholder');
            if (placeholder && map[placeholder]) {
                el.setAttribute('placeholder', map[placeholder]);
            }
            if (el.tagName.toLowerCase() === 'select') {
                Array.from(el.options).forEach(option => {
                    const text = option.text.trim();
                    if (map[text]) {
                        option.text = map[text];
                    }
                });
            }
        });
    }

    function translatePage(lang) {
        const map = translations[lang] || translations[defaultLang];
        translateTextNodes(document.body, map);
        translatePlaceholders(lang);
        setDirection(lang);
    }

    function createLanguageSwitcher() {
        const target = document.querySelector('.topbar .topbar-right') || document.querySelector('.navbar');
        if (!target || document.querySelector('.language-switcher')) return;

        const container = document.createElement('div');
        container.className = 'language-switcher';
        container.style.display = 'flex';
        container.style.gap = '8px';
        container.style.alignItems = 'center';
        container.style.marginLeft = '12px';

        const label = document.createElement('label');
        label.textContent = 'Lang:';
        label.style.fontWeight = '600';
        label.style.fontSize = '0.9rem';
        label.style.marginRight = '6px';
        label.htmlFor = 'language-select';
        container.appendChild(label);

        const select = document.createElement('select');
        select.id = 'language-select';
        select.style.padding = '6px 10px';
        select.style.border = '1px solid rgba(0,0,0,0.15)';
        select.style.borderRadius = '6px';
        select.style.background = 'white';
        select.style.color = '#333';
        select.style.fontSize = '0.9rem';
        select.style.cursor = 'pointer';

        supportedLanguages.forEach(code => {
            const option = document.createElement('option');
            option.value = code;
            option.textContent = code.toUpperCase();
            select.appendChild(option);
        });

        select.addEventListener('change', () => {
            const lang = select.value;
            saveLanguage(lang);
            translatePage(lang);
        });

        container.appendChild(select);
        target.prepend(container);
        updateLanguageSelect(getSavedLanguage());
    }

    function updateLanguageSelect(lang) {
        const select = document.getElementById('language-select');
        if (!select) return;
        select.value = lang;
    }

    function init() {
        const lang = getSavedLanguage();
        createLanguageSwitcher();
        translatePage(lang);
    }

    return { init };
})();

document.addEventListener('DOMContentLoaded', () => LanguageSwitcher.init());