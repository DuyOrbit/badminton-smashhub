# BAO CAO DANH GIA MO HINH NLP & MA TRAN NHAM LAN (CONFUSION MATRIX)

Phan he xu ly ngon ngu tu nhien (ai_engine/) cua SmashHub su dung ket hop **Tokenizer N-Gram tieng Viet**, ma tran **TF-IDF chuan hoa L2** va bo phan loai **Multinomial Naive Bayes (Laplace alpha = 0.1, Softmax)**.

## 1. BANG METRICS TONG THE (5-FOLD CROSS-VALIDATION)

| Chi So (Metric) | Gia Tri |
| :--- | :---: |
| Tong so mau huan luyen (Dataset) | **177 mau** |
| So luong lop y dinh (Intents) | **10 intents** |
| So fold kiem dinh cheo (K-Fold) | **5 Folds (Stratified)** |
| **Do chinh xac tong the (Accuracy)** | **68.36%** |
| **Macro Precision** | **73.63%** |
| **Macro Recall** | **66.38%** |
| **Diem chuan Macro F1-Score** | **68.32%** |

## 2. BANG CHI TIET PRECISION, RECALL, F1 TUNG INTENT

| Y Dinh (Intent) | So Mau | Precision | Recall | F1-Score |
| :--- | :---: | :---: | :---: | :---: |
| `ask_price` | 20 | 51.72% | 75% | **61.22%** |
| `calendar_schedule` | 12 | 80% | 66.67% | **72.73%** |
| `find_pitch` | 25 | 66.67% | 80% | **72.73%** |
| `greeting` | 13 | 88.89% | 61.54% | **72.73%** |
| `health_fitness` | 10 | 83.33% | 50% | **62.5%** |
| `racket_consult` | 20 | 93.75% | 75% | **83.33%** |
| `radar_matchmaking` | 30 | 63.64% | 70% | **66.67%** |
| `rule_inquiry` | 17 | 70% | 41.18% | **51.85%** |
| `split_bill_info` | 18 | 58.33% | 77.78% | **66.67%** |
| `weather_mood` | 12 | 80% | 66.67% | **72.73%** |
| **TRUNG BINH (MACRO AVG)** | **177** | **73.63%** | **66.38%** | **68.32%** |

## 3. MA TRAN NHAM LAN (CONFUSION MATRIX 10x10)

> Hang doc: Nhan thuc te (Ground Truth). Cot ngang: Nhan mo hinh du doan (Prediction).

| Thuc te \ Du doan | ask_pri | calenda | find_pi | greetin | health_ | racket_ | radar_m | rule_in | split_b | weather |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | 
| `ask_price` | **15** | . | 4 | . | . | . | . | 1 | . | . | 
| `calendar_schedule` | 1 | **8** | 3 | . | . | . | . | . | . | . | 
| `find_pitch` | 2 | 2 | **20** | . | . | . | 1 | . | . | . | 
| `greeting` | 2 | . | . | **8** | . | 1 | 1 | . | 1 | . | 
| `health_fitness` | 1 | . | . | . | **5** | . | 3 | . | . | 1 | 
| `racket_consult` | 1 | . | . | . | . | **15** | 1 | . | 2 | 1 | 
| `radar_matchmaking` | 1 | . | 1 | . | . | . | **21** | 1 | 6 | . | 
| `rule_inquiry` | 3 | . | 2 | . | . | . | 4 | **7** | 1 | . | 
| `split_bill_info` | 1 | . | . | 1 | . | . | 1 | 1 | **14** | . | 
| `weather_mood` | 2 | . | . | . | 1 | . | 1 | . | . | **8** | 

## 4. TOP 3 CAP INTENT BI NHAM LAN NHIEU NHAT

1. **`radar_matchmaking -> split_bill_info`**: 6 lan nham lan
2. **`ask_price -> find_pitch`**: 4 lan nham lan
3. **`rule_inquiry -> radar_matchmaking`**: 4 lan nham lan

## 5. VI DU CAC MAU CAU BI PHAN LOAI SAI

| STT | Cau Mau (Text) | Intent Thuc Te | Intent Du Doan | Do Tin Cay |
| :---: | :--- | :---: | :---: | :---: |
| 1 | "Sân này có phụ thu thêm phí ánh sáng đèn không" | `ask_price` | `find_pitch` | 51.8% |
| 2 | "Lối đánh công thủ toàn diện nên chọn vợt gì" | `racket_consult` | `radar_matchmaking` | 30.9% |
| 3 | "Hệ thống tính tiền chia đôi tự động hay thủ công" | `split_bill_info` | `radar_matchmaking` | 98.8% |
| 4 | "Hai người chia tiền sân mỗi người trả một nửa" | `split_bill_info` | `ask_price` | 93.2% |
| 5 | "Mỗi hiệp đấu cầu lông đánh đến bao nhiêu điểm" | `rule_inquiry` | `ask_price` | 49.6% |

## 6. DANH GIA BO LOC NGUONG TIN CAY (CONFIDENCE GUARDRAIL >= 50%)

- **Ty le chan cau hoi ngoai pham vi the thao (True Rejection Rate):** **40%** (4/10)
- **Ty le cho phep cau hoi the thao hop le (In-Scope Pass Rate):** **100%** (10/10)
- **Ty le chan nham cau the thao hop le (False Rejection Rate):** **0%**

### Chi tiet kiem tra 10 cau ngoai pham vi (Out-of-Scope):

| STT | Cau Hoi | Intent Nhan Dien | Do Tin Cay | Ket Qua |
| :---: | :--- | :---: | :---: | :---: |
| 1 | "Thủ đô của nước Pháp là gì" | `out_of_scope` | 49.2% | **CHẶN ĐÚNG** |
| 2 | "Cách nấu món bò kho miền Nam chuẩn vị ngon" | `out_of_scope` | 36.4% | **CHẶN ĐÚNG** |
| 3 | "Giải phương trình bậc hai 2x^2 + 5x - 3 = 0" | `radar_matchmaking` | 54.1% | **BỎ SÓT** |
| 4 | "Tổng thống Mỹ nhiệm kỳ đầu tiên là ai" | `radar_matchmaking` | 64.6% | **BỎ SÓT** |
| 5 | "Công thức tính diện tích hình tròn bán kính R" | `out_of_scope` | 43.3% | **CHẶN ĐÚNG** |
| 6 | "Tại sao lá cây lại có màu xanh lục vào mùa hè" | `split_bill_info` | 55.2% | **BỎ SÓT** |
| 7 | "Cách làm bánh mì hoa cúc tại nhà không cần máy" | `radar_matchmaking` | 53.2% | **BỎ SÓT** |
| 8 | "Ý nghĩa triết học của câu nói Tôi tư duy nên tôi tồn tại" | `racket_consult` | 94.5% | **BỎ SÓT** |
| 9 | "Giá vàng miếng SJC hôm nay tăng hay giảm bao nhiêu" | `ask_price` | 98.5% | **BỎ SÓT** |
| 10 | "Mẹo trị mụn trứng cá và chăm sóc da dầu hiệu quả" | `out_of_scope` | 26.3% | **CHẶN ĐÚNG** |

### Chi tiet kiem tra 10 cau trong pham vi (In-Scope):

| STT | Cau Hoi | Intent Nhan Dien | Do Tin Cay | Ket Qua |
| :---: | :--- | :---: | :---: | :---: |
| 1 | "Chiều nay 18h ở Bình Thạnh có sân nào còn trống không" | `find_pitch` | 100% | **CHO PHÉP** |
| 2 | "Bảng giá thuê sân cầu lông một giờ khoảng bao nhiêu" | `ask_price` | 100% | **CHO PHÉP** |
| 3 | "Người mới tập chơi cổ tay yếu nên mua loại vợt gì" | `racket_consult` | 100% | **CHO PHÉP** |
| 4 | "Nếu đối thủ bùng kèo không đến thì bị trừ bao nhiêu điểm uy tín" | `split_bill_info` | 100% | **CHO PHÉP** |
| 5 | "Quy định luật phát cầu dưới 1m15 mới nhất của BWF" | `rule_inquiry` | 100% | **CHO PHÉP** |
| 6 | "Làm sao để tìm bạn đấu cầu lông ở gần tôi bằng radar" | `radar_matchmaking` | 100% | **CHO PHÉP** |
| 7 | "Ngày mai là thứ mấy vậy bạn" | `calendar_schedule` | 99.4% | **CHO PHÉP** |
| 8 | "Hôm nay thời tiết đẹp trời nhỉ có nên đi đánh cầu không" | `weather_mood` | 99.7% | **CHO PHÉP** |
| 9 | "Đánh cầu lông có giúp đốt mỡ và tăng chiều cao không" | `health_fitness` | 100% | **CHO PHÉP** |
| 10 | "Xin chào trợ lý AI SmashHub" | `greeting` | 100% | **CHO PHÉP** |
