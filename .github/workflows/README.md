# Automatic APK builds (GitHub Actions)

Every time code is pushed, GitHub builds the app into an installable Android APK for you —
**no Visual Studio, no computer setup needed.**

## How to download your APK
1. Open your repo on GitHub ▸ **Actions** tab.
2. Click the most recent **"Build Android APK"** run (green ✓ = success).
3. Scroll to **Artifacts** ▸ download **`hardware-shop-apk`** (a .zip containing the `.apk`).
4. Unzip, copy the `.apk` to your Android phone, tap to install
   (allow "Install from unknown sources" if asked).

> The first build takes ~5–10 minutes (it installs the Android tools). Later builds are faster.

## Run it manually
Actions tab ▸ **Build Android APK** ▸ **Run workflow** button.

## This is a test (debug) build
The APK is signed with a debug key — perfect for testing on your own phone.
For a **Play Store / release** build signed with your own keystore, tell me and I'll add a
signed-release job that uses encrypted GitHub Secrets for your keystore.

---

## بالعربي
في كل مرة يتم فيها رفع الكود، تقوم GitHub ببناء ملف APK تلقائيًا — بدون الحاجة لأي برنامج على جهازك.

1. افتح المستودع على GitHub ثم تبويب **Actions**.
2. افتح آخر عملية **"Build Android APK"** (علامة ✓ خضراء تعني النجاح).
3. من قسم **Artifacts** نزّل **`hardware-shop-apk`**.
4. فك الضغط، انقل ملف `.apk` إلى هاتفك، وثبّته.

> النسخة الحالية للتجربة (debug). لإصدار موقّع للنشر على المتجر، أخبرني لأضيف خطوة التوقيع.
