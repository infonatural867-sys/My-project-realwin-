# REALWIN Website — InfinityFree setup (বাংলা)

## বর্তমান অবস্থা
এটি REALWIN-এর প্রথম Web/PWA frontend release। এটি বিদ্যমান PHP API starter-এর সঙ্গে যুক্ত হওয়ার জন্য তৈরি। Live hosting-এ আপলোড বা database test এখনও করা হয়নি।

## Upload
1. InfinityFree File Manager খুলে `htdocs`-এ যান।
2. `site/` ফোল্ডারের ভিতরের ফাইলগুলো সরাসরি `htdocs/`-এ আপলোড করুন (site ফোল্ডারটি নিজে নয়)।
3. Repository-এর `backend/` ফোল্ডারটি `htdocs/backend/`-এ আপলোড করুন।
4. `backend/config.example.php` কপি করে `backend/config.php` বানান এবং Hosting-এর MySQL host, database name, username, password বসান। কোনো credentials GitHub বা chat-এ দেবেন না।
5. phpMyAdmin-এ `backend/schema.sql` একবার import করুন। আগে schema import করা থাকলে duplicate করে import করবেন না; প্রয়োজনীয় migration আলাদা করে চালান।
6. HTTPS চালু করুন। এরপর `https://YOUR-DOMAIN/backend/api/health.php` খুলে API status দেখুন।
7. প্রথম admin account তৈরি করতে Register page দিয়ে নিজের account খুলুন। তারপর phpMyAdmin-এ নিজের email দিয়ে একবার চালান:
   `UPDATE users SET role='admin' WHERE email='YOUR-REGISTERED-EMAIL';`
8. `https://YOUR-DOMAIN/admin.php` দিয়ে Admin Panel-এ প্রবেশ করুন।

## গুরুত্বপূর্ণ
- REALWIN starter points হলো non-cash, non-transferable entertainment score; কোনো deposit, withdrawal, wager, cash prize বা redemption নেই।
- Public launch-এর আগে privacy/terms/grievance details, age/user-safety controls, applicable classification/registration এবং qualified legal review সম্পন্ন করতে হবে।
- InfinityFree shared hosting-এ PHP extensions, API access, resource limits ও security controls যাচাই না করে sensitive production user data রাখবেন না।
- Admin password, database credentials বা API secrets কাউকে পাঠাবেন না।
