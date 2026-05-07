<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$I18N_SUPPORTED_LANGS = ['fr', 'en', 'ar'];

$I18N_TR = [
    'fr' => [
        // Shared
        'home' => 'Accueil',
        'insurances' => 'Assurances',
        'doctors' => 'Médecins',
        'contact' => 'Contact',
        'login' => 'Connexion',
        'signup' => "S'inscrire",
        'cancel' => 'Annuler',
        'compare' => 'Comparer',
        'dark_mode' => 'Mode sombre',
        'footer_rights' => '© 2025 ASCLEPIA. Tous droits réservés.',
        'footer_made_by' => "Fait avec ❤️ par l'équipe ASCLEPIA",
        'months' => 'mois',
        'dt' => 'DT',
        'per_month_short' => '/ mois',
        'per_month_long' => 'DT / mois',

        // assurancefront.php
        'assurancefront_title' => 'Nos Assurances - ASCLEPIA',
        'offers' => '🛡️ Nos offres',
        'choose_health_insurance' => 'Choisissez votre assurance santé',
        'subtitle' => 'Des formules adaptées à chaque besoin, pour vous et votre famille.',
        'compare_title' => '⚖️ Comparaison des assurances',
        'criterion' => 'Critère',
        'type' => 'Type',
        'price_per_month' => 'Prix / mois',
        'duration' => 'Durée',
        'refund' => 'Remboursement',
        'clear_compare' => 'Effacer la comparaison',
        'search_placeholder' => 'Rechercher une assurance...',
        'all' => 'Tous',
        'refund_label' => 'Remboursement :',
        'duration_label' => 'Durée :',
        'add_compare' => 'Ajouter à la comparaison',
        'subscribe' => 'Souscrire',
        'no_result_title' => 'Aucune assurance trouvée',
        'compare_label' => 'Comparer :',
        'alert_only_2' => 'Vous pouvez comparer seulement 2 assurances !',
        'page' => 'Page',
        'on' => 'sur',

        // souscrireContrat.php
        'souscrire_title' => 'Souscrire - ASCLEPIA',
        'badge_contract' => '📄 Contrat',
        'hero_subscribe_title' => 'Souscrire à une assurance',
        'hero_subscribe_subtitle' => 'Remplissez le formulaire ci-dessous pour souscrire à votre assurance.',
        'success_title' => 'Souscription réussie !',
        'success_text_1' => 'Votre contrat a été créé avec succès. Il est maintenant',
        'active' => 'Actif',
        'new_contract' => 'Nouveau contrat',
        'see_insurances' => 'Voir les assurances',
        'err_required' => 'Veuillez remplir tous les champs obligatoires.',
        'form_title' => 'Formulaire de souscription',
        'choose_insurance' => 'Choisir une assurance',
        'select_insurance' => '-- Sélectionner une assurance --',
        'err_choose_insurance' => 'Veuillez choisir une assurance.',
        'details_title' => "Détails de l'assurance sélectionnée",
        'monthly_price' => 'Prix mensuel',
        'start_date' => 'Date de début',
        'err_start_date' => 'La date de début est requise.',
        'end_date' => 'Date de fin',
        'optional' => '(optionnel)',
        'err_end_date' => 'La date de fin doit être après la date de début.',
        'total_amount' => 'Montant total (DT)',
        'auto_calc' => 'Calculé automatiquement',
        'hint_calc' => "Calculé automatiquement selon l'assurance et la durée.",
        'err_amount' => 'Le montant doit être supérieur à 0.',
        'confirm' => 'Confirmer la souscription',
        // mesContrats.php
        'my_contracts' => 'Mes contrats',
        'active_contracts' => 'Contrats actifs',
        'realtime_subtitle' => 'Statut en temps réel + progression visuelle.',
        'no_contract' => 'Aucun contrat actif pour le moment.',
        'type_label' => 'Type :',
        'amount_label' => 'Montant :',
        'start_label' => 'Début :',
        'end_label' => 'Fin :',
        'mes_contrats_btn' => 'Mes contrats',
    ],
    'en' => [
        // Shared
        'home' => 'Home',
        'insurances' => 'Insurances',
        'doctors' => 'Doctors',
        'contact' => 'Contact',
        'login' => 'Sign in',
        'signup' => 'Sign up',
        'cancel' => 'Cancel',
        'compare' => 'Compare',
        'dark_mode' => 'Dark mode',
        'footer_rights' => '© 2025 ASCLEPIA. All rights reserved.',
        'footer_made_by' => 'Made with ❤️ by the ASCLEPIA team',
        'months' => 'months',
        'dt' => 'DT',
        'per_month_short' => '/ month',
        'per_month_long' => 'DT / month',

        // assurancefront.php
        'assurancefront_title' => 'Our Insurances - ASCLEPIA',
        'offers' => '🛡️ Our offers',
        'choose_health_insurance' => 'Choose your health insurance',
        'subtitle' => 'Plans tailored to every need, for you and your family.',
        'compare_title' => '⚖️ Insurance comparison',
        'criterion' => 'Criteria',
        'type' => 'Type',
        'price_per_month' => 'Price / month',
        'duration' => 'Duration',
        'refund' => 'Reimbursement',
        'clear_compare' => 'Clear comparison',
        'search_placeholder' => 'Search an insurance...',
        'all' => 'All',
        'refund_label' => 'Reimbursement:',
        'duration_label' => 'Duration:',
        'add_compare' => 'Add to comparison',
        'subscribe' => 'Subscribe',
        'no_result_title' => 'No insurance found',
        'compare_label' => 'Compare:',
        'alert_only_2' => 'You can compare only 2 insurances!',
        'page' => 'Page',
        'on' => 'of',

        // souscrireContrat.php
        'souscrire_title' => 'Subscribe - ASCLEPIA',
        'badge_contract' => '📄 Contract',
        'hero_subscribe_title' => 'Subscribe to an insurance',
        'hero_subscribe_subtitle' => 'Fill in the form below to subscribe to your insurance.',
        'success_title' => 'Subscription successful!',
        'success_text_1' => 'Your contract has been created successfully. It is now',
        'active' => 'Active',
        'new_contract' => 'New contract',
        'see_insurances' => 'View insurances',
        'err_required' => 'Please fill in all required fields.',
        'form_title' => 'Subscription form',
        'choose_insurance' => 'Choose an insurance',
        'select_insurance' => '-- Select an insurance --',
        'err_choose_insurance' => 'Please choose an insurance.',
        'details_title' => 'Selected insurance details',
        'monthly_price' => 'Monthly price',
        'start_date' => 'Start date',
        'err_start_date' => 'Start date is required.',
        'end_date' => 'End date',
        'optional' => '(optional)',
        'err_end_date' => 'End date must be after start date.',
        'total_amount' => 'Total amount (DT)',
        'auto_calc' => 'Auto-calculated',
        'hint_calc' => 'Automatically calculated based on insurance and duration.',
        'err_amount' => 'Amount must be greater than 0.',
        'confirm' => 'Confirm subscription',
        // mesContrats.php
        'my_contracts' => 'My contracts',
        'active_contracts' => 'Active contracts',
        'realtime_subtitle' => 'Real-time status + visual progress.',
        'no_contract' => 'No active contracts at the moment.',
        'type_label' => 'Type:',
        'amount_label' => 'Amount:',
        'start_label' => 'Start:',
        'end_label' => 'End:',
        'mes_contrats_btn' => 'My contracts',
    ],
    'ar' => [
        // Shared
        'home' => 'الرئيسية',
        'insurances' => 'التأمينات',
        'doctors' => 'الأطباء',
        'contact' => 'اتصل بنا',
        'login' => 'تسجيل الدخول',
        'signup' => 'إنشاء حساب',
        'cancel' => 'إلغاء',
        'compare' => 'قارن',
        'dark_mode' => 'الوضع الداكن',
        'footer_rights' => '© 2025 ASCLEPIA. جميع الحقوق محفوظة.',
        'footer_made_by' => 'صُنع بـ ❤️ بواسطة فريق ASCLEPIA',
        'months' => 'شهر',
        'dt' => 'د.ت',
        'per_month_short' => '/ شهر',
        'per_month_long' => 'د.ت / شهر',

        // assurancefront.php
        'assurancefront_title' => 'تأميناتنا - ASCLEPIA',
        'offers' => '🛡️ عروضنا',
        'choose_health_insurance' => 'اختر تأمينك الصحي',
        'subtitle' => 'خطط مناسبة لكل احتياج لك ولعائلتك.',
        'compare_title' => '⚖️ مقارنة التأمينات',
        'criterion' => 'المعيار',
        'type' => 'النوع',
        'price_per_month' => 'السعر / شهر',
        'duration' => 'المدة',
        'refund' => 'نسبة التعويض',
        'clear_compare' => 'مسح المقارنة',
        'search_placeholder' => 'ابحث عن تأمين...',
        'all' => 'الكل',
        'refund_label' => 'التعويض:',
        'duration_label' => 'المدة:',
        'add_compare' => 'أضف للمقارنة',
        'subscribe' => 'اشترك',
        'no_result_title' => 'لم يتم العثور على تأمين',
        'compare_label' => 'قارن:',
        'alert_only_2' => 'يمكنك مقارنة تأمينين فقط!',
        'page' => 'الصفحة',
        'on' => 'من',

        // souscrireContrat.php
        'souscrire_title' => 'الاشتراك - ASCLEPIA',
        'badge_contract' => '📄 عقد',
        'hero_subscribe_title' => 'الاشتراك في تأمين',
        'hero_subscribe_subtitle' => 'املأ النموذج أدناه للاشتراك في التأمين.',
        'success_title' => 'تم الاشتراك بنجاح!',
        'success_text_1' => 'تم إنشاء عقدك بنجاح. وهو الآن',
        'active' => 'نشط',
        'new_contract' => 'عقد جديد',
        'see_insurances' => 'عرض التأمينات',
        'err_required' => 'يرجى تعبئة جميع الحقول المطلوبة.',
        'form_title' => 'نموذج الاشتراك',
        'choose_insurance' => 'اختر تأميناً',
        'select_insurance' => '-- اختر تأميناً --',
        'err_choose_insurance' => 'يرجى اختيار تأمين.',
        'details_title' => 'تفاصيل التأمين المختار',
        'monthly_price' => 'السعر الشهري',
        'start_date' => 'تاريخ البداية',
        'err_start_date' => 'تاريخ البداية مطلوب.',
        'end_date' => 'تاريخ النهاية',
        'optional' => '(اختياري)',
        'err_end_date' => 'يجب أن يكون تاريخ النهاية بعد تاريخ البداية.',
        'total_amount' => 'المبلغ الإجمالي (د.ت)',
        'auto_calc' => 'يُحسب تلقائياً',
        'hint_calc' => 'يُحسب تلقائياً حسب التأمين والمدة.',
        'err_amount' => 'يجب أن يكون المبلغ أكبر من 0.',
        'confirm' => 'تأكيد الاشتراك',
        // mesContrats.php
        'my_contracts' => 'عقودي',
        'active_contracts' => 'العقود النشطة',
        'realtime_subtitle' => 'الحالة في الوقت الفعلي + تقدم مرئي.',
        'no_contract' => 'لا توجد عقود نشطة في الوقت الحالي.',
        'type_label' => 'النوع:',
        'amount_label' => 'المبلغ:',
        'start_label' => 'البداية:',
        'end_label' => 'النهاية:',
        'mes_contrats_btn' => 'عقودي',
    ],
];

if (!function_exists('i18n_boot')) {
    function i18n_boot($defaultLang = 'fr') {
        global $I18N_SUPPORTED_LANGS;

        if (isset($_GET['lang']) && in_array($_GET['lang'], $I18N_SUPPORTED_LANGS, true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        $lang = $_SESSION['lang'] ?? $defaultLang;
        if (!in_array($lang, $I18N_SUPPORTED_LANGS, true)) {
            $lang = $defaultLang;
        }

        return [
            'lang' => $lang,
            'isRtl' => ($lang === 'ar'),
        ];
    }
}

if (!function_exists('i18n_t')) {
    function i18n_t($key, $lang = 'fr') {
        global $I18N_TR;
        return $I18N_TR[$lang][$key] ?? $I18N_TR['fr'][$key] ?? $key;
    }
}

if (!function_exists('i18n_lang_url')) {
    function i18n_lang_url($newLang) {
        $params = $_GET;
        $params['lang'] = $newLang;
        return basename($_SERVER['PHP_SELF']) . '?' . http_build_query($params);
    }
}

if (!function_exists('i18n_t')) {
    function i18n_t($key, $lang = 'fr') {
        global $I18N_TR;
        return $I18N_TR[$lang][$key] ?? $I18N_TR['fr'][$key] ?? $key;
    }
}

if (!function_exists('i18n_lang_url')) {
    function i18n_lang_url($newLang) {
        $params = $_GET;
        $params['lang'] = $newLang;
        return basename($_SERVER['PHP_SELF']) . '?' . http_build_query($params);
    }
}

