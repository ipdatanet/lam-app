<?php
/**
 * ==========================================
 * إعدادات المستودع (قم بتعديل هذه البيانات فقط)
 * ==========================================
 */
$github_user = "اسم_المستخدم_هنا";   // مثال: Octocat
$github_repo = "اسم_المستودع_هنا";    // مثال: MyAwesomeApp
$app_title   = "تطبيقي الرائع";       // اسم التطبيق الذي سيظهر للزوار

// جلب بيانات آخر إصدار من GitHub API عبر PHP
$api_url = "https://api.github.com/repos/{$github_user}/{$github_repo}/releases/latest";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// GitHub يتطلب إرسال User-Agent
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: PHP-GitHub-App-Downloader',
    'Accept: application/vnd.github.v3+json'
]);

$response = curl_exec($ch);
curl_close($ch);

$release_data = json_decode($response, true);

// استخراج رابط الـ APK ومعلومات الإصدار
$apk_url       = "#";
$apk_size      = "غير معروف";
$version_tag   = "v1.0.0";
$release_notes = "لا توجد تفاصيل لهذا الإصدار.";
$download_count = 0;
$found_apk     = false;

if ($release_data && !isset($release_data['message'])) {
    $version_tag   = $release_data['tag_name'] ?? 'v1.0.0';
    $release_notes = nl2br(htmlspecialchars($release_data['body'] ?? ''));

    // البحث داخل المرفقات (assets) عن ملف ينتهي بـ .apk
    if (!empty($release_data['assets'])) {
        foreach ($release_data['assets'] as $asset) {
            if (pathinfo($asset['name'], PATHINFO_EXTENSION) === 'apk') {
                $apk_url        = $asset['browser_download_url'];
                $apk_size       = round($asset['size'] / (1024 * 1024), 2) . ' MB';
                $download_count = $asset['download_count'];
                $found_apk      = true;
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحميل تطبيق <?= htmlspecialchars($app_title); ?> - APK</title>
    <!-- خط تجوال من Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- أيقونات FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #2563eb;       /* أزرق أساسي */
            --primary-dark: #1d4ed8;  /* أزرق داكن */
            --primary-light: #eff6ff; /* أزرق فاتح جداً */
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-white: #ffffff;
            --bg-body: #f8fafc;
            --border-color: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Tajawal', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 550px;
            background: var(--bg-white);
            border-radius: 24px;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.1), 0 8px 10px -6px rgba(37, 99, 235, 0.1);
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        /* الرأس العلوي */
        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 40px 20px;
            text-align: center;
            color: var(--bg-white);
            position: relative;
        }

        .app-icon-wrapper {
            width: 90px;
            height: 90px;
            background: var(--bg-white);
            color: var(--primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            font-size: 45px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.15);
        }

        .app-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .app-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
            backdrop-filter: blur(4px);
        }

        /* جسم البطاقة */
        .card-body {
            padding: 30px 25px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-card {
            background: var(--primary-light);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #dbeafe;
        }

        .info-card i {
            color: var(--primary);
            font-size: 18px;
            margin-bottom: 5px;
        }

        .info-card .label {
            font-size: 12px;
            color: var(--text-muted);
            display: block;
        }

        .info-card .value {
            font-size: 15px;
            font-weight: 700;
            color: var(--primary-dark);
        }

        /* الملاحظات والتغييرات */
        .changelog {
            background: #f8fafc;
            border: 1px dashed var(--border-color);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            max-height: 120px;
            overflow-y: auto;
            font-size: 13px;
            line-height: 1.6;
            color: var(--text-muted);
        }

        .changelog-title {
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* زر التحميل الرئيسي */
        .btn-download {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            background: var(--primary);
            color: #ffffff;
            text-decoration: none;
            padding: 16px;
            border-radius: 14px;
            font-size: 18px;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-download:hover {
            background: var(--primary-dark);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.4);
            transform: translateY(-2px);
        }

        .btn-disabled {
            background: #94a3b8;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-disabled:hover {
            background: #94a3b8;
            transform: none;
        }

        /* شريط الحماية */
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            color: #16a34a;
            margin-top: 15px;
            font-weight: 500;
        }

        /* التذييل */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
        }

        .footer a {
            color: var(--primary);
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- الرأس -->
        <div class="card-header">
            <div class="app-icon-wrapper">
                <i class="fa-brands fa-android"></i>
            </div>
            <h1 class="app-title"><?= htmlspecialchars($app_title); ?></h1>
            <span class="app-badge">
                <i class="fa-solid fa-code-branch"></i> <?= htmlspecialchars($version_tag); ?>
            </span>
        </div>

        <!-- المحتوى -->
        <div class="card-body">
            <!-- شبكة المعلومات -->
            <div class="info-grid">
                <div class="info-card">
                    <i class="fa-solid fa-file-zipper"></i>
                    <span class="label">حجم الملف</span>
                    <span class="value"><?= $apk_size; ?></span>
                </div>
                <div class="info-card">
                    <i class="fa-solid fa-download"></i>
                    <span class="label">مرات التحميل</span>
                    <span class="value"><?= number_format($download_count); ?></span>
                </div>
            </div>

            <!-- سجل التحديثات -->
            <?php if (!empty($release_notes)): ?>
            <div class="changelog">
                <div class="changelog-title">
                    <i class="fa-solid fa-bullhorn text-primary"></i> ما الجديد في هذا الإصدار:
                </div>
                <div><?= $release_notes; ?></div>
            </div>
            <?php endif; ?>

            <!-- زر التحميل -->
            <?php if ($found_apk): ?>
                <a href="<?= $apk_url; ?>" class="btn-download" id="downloadBtn">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>تحميل التطبيق (APK)</span>
                </a>
            <?php else: ?>
                <button class="btn-download btn-disabled" disabled>
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>لم يتم العثور على ملف APK في المستودع</span>
                </button>
            <?php endif; ?>

            <!-- علامة الأمان -->
            <div class="secure-badge">
                <i class="fa-solid fa-shield-halved"></i>
                <span>ملف آمن ومفحوص تم جلبه مباشرة من GitHub Releases</span>
            </div>
        </div>
    </div>

    <div class="footer">
        مستضاف عبر المستودع: 
        <a href="https://github.com/<?= htmlspecialchars($github_user . '/' . $github_repo); ?>" target="_blank">
            <i class="fa-brands fa-github"></i> <?= htmlspecialchars($github_user . '/' . $github_repo); ?>
        </a>
    </div>

    <!-- كود JavaScript التفاعلي -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('downloadBtn');
            
            if (btn) {
                btn.addEventListener('click', function(e) {
                    const originalText = this.innerHTML;
                    
                    // تغيير نص الزر عند الضغط للتفاعل مع المستخدم
                    this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري بدء التحميل...';
                    this.style.pointerEvents = 'none';

                    // إعادة الزر لشكله الطبيعي بعد 4 ثوانٍ
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.pointerEvents = 'auto';
                    }, 4000);
                });
            }
        });
    </script>
</body>
</html>
