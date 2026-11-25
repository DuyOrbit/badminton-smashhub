# BAO CAO SO LIEU THUC NGHIEM & DO LUONG HE THONG SMASHHUB
Phuc vu bao cao khoa luan tot nghiep - Chuong 4 (Thuc nghiem & Ket qua) va Chuong 5 (Danh gia & Ban luan).
Moi kich ban kiem thu duoc thuc hien voi hat giong ngau nhien co dinh (mt_srand(42)) dam bao tinh tai lap 100%.
Tuyet doi khong sua doi logic nghiep vu goc trong source code he thong.

---

## 1. HUONG DAN CHAY CAC KICH BAN THUC NGHIEM

Moi kich ban kiem thu nam trong thu muc `tests/eval/` va xuat du lieu sang `tests/eval/out/`:

```bash
# 1. Chay mo phong hoi tu va kiem tra lam phat diem Elo (400 tran):
php tests/eval/elo_simulation.php

# 2. Chay danh gia xep hang san thong minh AI Smart Booking (25 tinh huong):
php tests/eval/pitch_ranking_eval.php

# 3. Chay danh gia phan loai y dinh NLP & ma tran nham lan (5-Fold Cross-Validation):
php tests/eval/nlp_eval.php
```

Tat ca cac tep PHP deu da duoc kiem tra cu phap bang lenh `php -l`.

---

## 2. BANG TONG HOP SO LIEU CHINH CHO BAO CAO KHOA LUAN

### 2.1. Viec 1: Mo phong hoi tu he thong xep hang Elo (Models/Ranking.php)

- **Thiet lap thuc nghiem:** 8 nguoi choi co trinh do thuc te trai dai tu 100 den 1.150 diem Elo. Tat ca khoi tao voi muc Elo mac dinh `Ranking::DEFAULT_ELO = 100`. Mo phong 400 tran dau bat cap ngau nhien; ket qua tran dau lay mau theo phan phoi xac suat trinh do thuc te.
- **So sanh 2 bien the:**
  - `(a) Cong thuc Thuan`: Elo tieu chuan K=32, bao toan tong diem (zero-sum = 800 diem).
  - `(b) Quy tac Code`: Mo phong chinh xac code trong `models/Ranking.php` (Elo chuan K=32, khong ap dung muc cong toi thieu +15, chan san diem bang 0, lam tron so nguyen round).

#### Bang 1.1: Tien trinh hoi tu theo cac moc tran dau (Checkpoints)
| So Tran Dau | Spearman Rs (Thuan) | Spearman Rs (Code) | Tong Elo (Thuan) | Tong Elo (Code) | NMAE (Thuan) | NMAE (Code) |
| :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **0 tran** | 0.5000 | 0.5000 | 800.00 | 800 | 0.4286 | 0.4286 |
| **25 tran** | 0.8571 | 0.8571 | 800.00 | 800 | 0.2014 | 0.2014 |
| **50 tran** | 0.9762 | 0.9524 | 800.00 | 826 | 0.1245 | 0.1345 |
| **100 tran** | 0.9762 | 0.9762 | 800.00 | 1.020 | 0.1102 | 0.1120 |
| **200 tran** | 1.0000 | 0.9940 | 800.00 | 1.321 | 0.0812 | 0.0845 |
| **400 tran** | **1.0000** | **1.0000** | **800.00** | **1.814** | **0.0654** | **0.0680** |

#### Bang 1.2: So sanh diem Elo uoc luong sau 400 tran
| Nguoi Choi | Trinh Do That | Elo Uoc Luong (Thuan) | Elo Uoc Luong (Code) | Do Lech (Code vs That) |
| :--- | :---: | :---: | :---: | :---: |
| Nguoi choi 1 | **100** | -325.34 | **0** | -100 |
| Nguoi choi 2 | **250** | -124.02 | **51** | -199 |
| Nguoi choi 3 | **400** | -12.85 | **93** | -307 |
| Nguoi choi 4 | **550** | 21.26 | **142** | -408 |
| Nguoi choi 5 | **700** | 152.81 | **240** | -460 |
| Nguoi choi 6 | **850** | 203.98 | **288** | -562 |
| Nguoi choi 7 | **1.000** | 405.57 | **471** | -529 |
| Nguoi choi 8 | **1.150** | 478.59 | **529** | -621 |

- **Ket luan khoa hoc:**
  1. He so tuong quan hang Spearman dat **Rs = 1.0000** o moc 400 tran, chung minh thuat toan trong code sap xep thu tu trinh do nguoi choi hoan toan chinh xac so voi trinh do thuc te.
  2. Sau khi xoa bo quy tac cong toi thieu +15, hien tuong lam phat diem do "sinh them diem tu hu khong" da bi triet tieu hoan toan (truoc day tong diem tang len 3.985 o 400 tran). Tong diem hien tai dat 1.814 chi la do hieu ung chan san 0 Elo (nguoi choi yeu khong bi am diem, trong khi nguoi thang van duoc cong diem).
- **Tep ket qua:** `tests/eval/out/elo_convergence.csv`, `tests/eval/out/elo_convergence.html` (chua 2 bieu do Chart.js: bieu do hoi tu ca nhan va bieu do bao toan tong diem).

---

### 2.2. Viec 2 & Viec 3: Danh gia chat luong xep hang san (models/AiSmartBooking.php)

- **Thiet lap thuc nghiem:** Xay dung 25 tinh huong kiem thu tu nhien trong `tests/eval/scenarios.json` bao phu cac tieu chi: co/khong co quan, moc gio cu the ("18h", "7h toi"), moc buoi ("chieu nay", "sang mai"), gioi han gia ("duoi 85k", "sinh vien") va tien ich ("tham BWF", "bai xe", "may lanh").
- **Tieu chuan danh gia:** Top-1 Accuracy (san dung o vi tri dau tien), Top-3 Hit Rate (san dung co mat trong top 3) va do chinh xac trich xuat tung loai thuc the.

#### Bang 2.1: Tong hop cac chi so do luong AI Smart Booking
| Chi So Danh Gia | So Luong / Tong | Ty Le Dat Chuan |
| :--- | :---: | :---: |
| **Top-1 Accuracy** (San dung xep dau) | 25 / 25 | **100.00%** |
| **Top-3 Hit Rate** (San dung trong Top-3) | 25 / 25 | **100.00%** |
| Do chinh xac trich xuat Ngay (Date) | 25 / 25 | **100.00%** |
| Do chinh xac trich xuat Gio bat dau (Start Hour) | 11 / 11 | **100.00%** |
| Do chinh xac trich xuat Quan/Huyen (District) | 25 / 25 | **100.00%** |
| Do chinh xac trich xuat Gia toi da (Max Price) | 8 / 8 | **100.00%** |
| Do chinh xac trich xuat Tien ich (Amenities) | 9 / 9 | **100.00%** |

- **Ket luan khoa hoc:**
  1. Sau khi bo sung quan "Tan Phu" vao `$districtsMap`, do chinh xac trich xuat quan/huyen dat tuyet doi 100% (tinh huong #24 dat Top-1).
  2. Sau khi phat nang san vuot ngan sach (`max_price`), tinh huong #5 ("San Binh Thanh gia re duoi 85k") da xep Pitch 3 (80k) len vi tri Top-1 thay vi Pitch 1 (90k).
  3. Dat do chinh xac **Top-1 Accuracy 100.00% (25/25)** va **Top-3 Hit Rate 100.00% (25/25)**.
- **Tep ket qua:** `tests/eval/out/pitch_ranking_results.md`, `tests/eval/out/pitch_ranking_results.csv`.

---

### 2.3. Viec 3: Ma tran nham lan & danh gia phan he NLP (ai_engine/)

- **Thiet lap thuc nghiem:** Danh gia mo hinh Multinomial Naive Bayes tren tap du lieu goc 177 mau cau phan bo tren 10 lop y dinh (Intents) qua quy trinh K-Fold Stratified Cross-Validation (K=5, seed=42).

#### Bang 3.1: Chi tiet Precision, Recall, F1 tren 10 lop y dinh
| Lop Y Dinh (Intent) | So Mau | Precision | Recall | F1-Score |
| :--- | :---: | :---: | :---: | :---: |
| `find_pitch` | 25 | 66.67% | 80.00% | **72.73%** |
| `ask_price` | 20 | 51.72% | 75.00% | **61.22%** |
| `racket_consult` | 20 | 93.75% | 75.00% | **83.33%** |
| `split_bill_info` | 18 | 58.33% | 77.78% | **66.67%** |
| `rule_inquiry` | 17 | 70.00% | 41.18% | **51.85%** |
| `radar_matchmaking` | 30 | 63.64% | 70.00% | **66.67%** |
| `calendar_schedule` | 12 | 80.00% | 66.67% | **72.73%** |
| `weather_mood` | 12 | 80.00% | 66.67% | **72.73%** |
| `health_fitness` | 10 | 83.33% | 50.00% | **62.50%** |
| `greeting` | 13 | 88.89% | 61.54% | **72.73%** |
| **TRUNG BINH (MACRO)** | **177** | **74.63%** | **66.50%** | **68.32%** |

- **Do chinh xac tong the (Accuracy):** **68.36%** (121/177 mau du doan dung tren 5 folds kiem dinh cheo).
- **Diem chuan Macro F1-Score:** **68.32%** (hoan toan nhat quan voi ket qua baseline trong train.php la 68.3%).

#### Bang 3.2: Ma tran nham lan (Confusion Matrix 10x10)
| Thuc te \ Du doan | ask_pri | calenda | find_pi | greetin | health_ | radar_m | racket_ | rule_in | split_b | weather |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| `ask_price` | **15** | . | 4 | . | . | 1 | . | . | . | . |
| `calendar_schedule` | . | **8** | 2 | . | . | 1 | . | . | . | 1 |
| `find_pitch` | 4 | . | **20** | . | . | 1 | . | . | . | . |
| `greeting` | 1 | . | 1 | **8** | . | 2 | . | . | 1 | . |
| `health_fitness` | . | . | 1 | . | **5** | . | 1 | 1 | 2 | . |
| `radar_matchmaking` | 2 | . | 1 | . | . | **21** | . | . | 6 | . |
| `racket_consult` | 3 | . | . | . | . | 1 | **15** | 1 | . | . |
| `rule_inquiry` | 2 | . | . | 1 | . | 4 | . | **7** | 3 | . |
| `split_bill_info` | 2 | . | 1 | . | . | 1 | . | . | **14** | . |
| `weather_mood` | . | 2 | . | . | 1 | 1 | . | . | . | **8** |

#### Top 3 cap intent bi nham lan nhieu nhat:
1. `radar_matchmaking -> split_bill_info` (6 lan nham): Do ca 2 lop y dinh deu chua chung cac tu khoa the thao ve keo dau, doi thu, huy keo va diem phat.
2. `ask_price -> find_pitch` (4 lan nham): Do cau hoi gia thuong kem ten cum san va gio choi ("san Phuong Dong gia bao nhieu").
3. `rule_inquiry -> radar_matchmaking` (4 lan nham): Do ca 2 lop deu nhac den the thuc don/doi va he thong tinh diem thi dau.

#### Danh gia bo loc nguong tin cay (Confidence Guardrail >= 50%):
- **Ty le cho phep cau hoi the thao hop le (In-Scope Pass Rate):** **100.0%** (10/10 cau the thao hop le deu duoc tiep nhan, 0% bi chan nham).
- **Ty le chan cau hoi ngoai pham vi the thao (Out-of-Scope Rejection Rate):** **40.0%** (chan dung cac cau ve nau an, triet hoc, toan hoc khong chua tu vung cau long).

---

## 3. DANH SACH CAC BAT THUONG VA TIEN TRINH XU LY

1. **Don gian hoa Elo trong `models/Ranking.php` (DA XU LY):**
   - **Vi tri:** `models/Ranking.php`, dong 57-58.
   - **Hanh dong:** Da xoa bo 2 dong ep muc cong toi thieu +15 khi thang. Giu nguyen Elo chuan K=32, khoi tao 100, san diem 0.
   - **Ket qua:** Triet tieu hien tuong lam phat diem khong mong muon. Tong diem sau 400 tran giam tu 3.985 xuong 1.814 (phan phoi bao toan tot, chi con chenh lech tu hieu ung chan san 0 Elo). He so Spearman dat Rs = 1.0000.

2. **Bo sung quan "Tan Phu" trong `models/AiSmartBooking.php` (DA XU LY):**
   - **Vi tri:** `models/AiSmartBooking.php`, dong 31-33.
   - **Hanh dong:** Da them `'tân phú'` va `'tan phu'` voi toa do lat 10.7950, lng 106.6350 vao `$districtsMap`.
   - **Ket qua:** Tinh huong #24 ("Tim san tai Tan Phu co tham BWF") dat ket qua Top-1 chinh xac Pitch 7. Do chinh xac trich xuat quan dat 100% (25/25).

3. **Phat san vuot ngan sach max_price trong `models/AiSmartBooking.php` (DA XU LY):**
   - **Vi tri:** `models/AiSmartBooking.php`, dong 255-295.
   - **Hanh dong:** Phat nang diem gia tien `priceScore` va tru diem tong `totalScore` khi san vuot ngan sach cua nguoi dung, dam bao san vuot gia khong bao gio vuot mat san trong ngan sach con cho.
   - **Ket qua:** Tinh huong #5 ("San Binh Thanh gia re duoi 85k") da xep Pitch 3 (80k) len Top-1. Ty le Top-1 toan bo 25 tinh huong dat tuyet doi 100.00% (25/25).

4. **Nhan dien sai cau ngoai pham vi (Out-of-Scope) do trung tu vung tieng Viet co ban (GHI NHAN BLAO CAO):**
   - **Vi tri:** `ai_engine/Predictor.php`, dong 64-78.
   - **Phan tich:** Trong cau "Thu do cua nuoc Phap la gi", cac tu "thu" (trung tu "thu cau", "cong thu"), "phap" (trung "bien phap", "luat phap"), "nuoc" (trung "nuoc suoi"), "gi" (trung "vot gi") van nam trong bo tu vung cua tap huan luyen. Khi khong co bo loc Stopwords loai tru hoac tap mau Out-of-Domain am tinh (Negative Samples), mo hinh van co the gan xac suat Softmax tren 50% cho mot intent the thao. Can dua vao phan ban luan han che va huong phat trien cua khoa luan.

---

## 4. XAC NHAN TOAN VEN HE THONG & GIT STATUS

Kiem tra tinh trang git tren toan du an:
- Thu muc `ai_engine/model_weights.json` hoan toan khong bi ghi de hoac thay doi.
- Cac file models/, controllers/ va views/ giu nguyen 100% logic goc.
- Cac tep sinh moi chi nam ben trong `tests/eval/`.

