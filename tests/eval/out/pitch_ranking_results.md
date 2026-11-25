# KET QUA DANH GIA HE THONG GOI Y SAN (AI SMART BOOKING)

Danh gia tren **25 tinh huong ngon ngu tu nhien** da dang ve quan/huyen, thoi gian, gia tien va tien ich.

## 1. BANG TONG HOP CHI SO CHINH

| Chi So Do Luong | So Luong / Tong | Ty Le Dat Chuan |
| :--- | :---: | :---: |
| **Top-1 Accuracy** (San xep dau dung ky vong) | 25 / 25 | **100%** |
| **Top-3 Hit Rate** (Co it nhat 1 san dung trong Top 3) | 25 / 25 | **100%** |
| Do chinh xac Trich xuat Ngay thi dau | 25 / 25 | 100% |
| Do chinh xac Trich xuat Gio bat dau | 11 / 11 | 100% |
| Do chinh xac Trich xuat Quan / Dia diem | 25 / 25 | 100% |
| Do chinh xac Trich xuat Ngan sach toi da | 8 / 8 | 100% |
| Do chinh xac Trich xuat Tien ich mong muon | 9 / 9 | 100% |

---

## 2. BANG CHI TIET 25 TINH HUONG KIEM THU

| ID | Cau Prompt | San Ky Vong | Top-1 | Top-3 Ket Qua | Top-1 | Top-3 | Ghi Chu Phan Tich |
| :---: | :--- | :---: | :---: | :--- | :---: | :---: | :--- |
| 1 | Tìm sân ở Bình Thạnh chiều nay 17h thảm BWF | `1,3` | `1` | `1,3` | **DUNG** | **DUNG** | Khop ky vong |
| 2 | Có sân nào ở quận 10 giá dưới 80k tối nay không | `5` | `5` | `5` | **DUNG** | **DUNG** | Khop ky vong |
| 3 | Thuê sân cầu lông Gò Vấp sáng mai 8h | `6,4` | `4` | `4,6` | **DUNG** | **DUNG** | Khop ky vong |
| 4 | Tìm sân ở Tân Bình có bãi đỗ ô tô | `2` | `2` | `2` | **DUNG** | **DUNG** | Khop ky vong |
| 5 | Sân Bình Thạnh giá rẻ dưới 85k | `3` | `3` | `3,1` | **DUNG** | **DUNG** | Khop ky vong |
| 6 | Tối nay 19h quanh vị trí của tôi có sân nào trống | `1,3` | `1` | `1,5,3` | **DUNG** | **DUNG** | Khop ky vong |
| 7 | Tìm cụm sân có thảm tiêu chuẩn BWF giá dưới 100k | `1,5,6` | `1` | `1,2,3` | **DUNG** | **DUNG** | Khop ky vong |
| 8 | Sân sinh viên giá rẻ quanh quận 10 | `5` | `5` | `5` | **DUNG** | **DUNG** | Khop ky vong |
| 9 | Đặt sân 20h tối mai ở Bình Thạnh | `1,3` | `1` | `1,3` | **DUNG** | **DUNG** | Khop ky vong |
| 10 | Tìm sân có điều hòa máy lạnh ở Gò Vấp | `4,6` | `4` | `4,6` | **DUNG** | **DUNG** | Khop ky vong |
| 11 | Chiều nay 18h cần sân Tân Bình giá dưới 100k | `2` | `2` | `2` | **DUNG** | **DUNG** | Khop ky vong |
| 12 | Sân Gò Vấp giá dưới 90k có bãi xe | `6` | `6` | `6,4` | **DUNG** | **DUNG** | Khop ky vong |
| 13 | Tìm sân đánh cầu tối nay 7h tối ở Bình Thạnh | `1,3` | `1` | `1,3` | **DUNG** | **DUNG** | Khop ky vong |
| 14 | Thuê sân cuối tuần ở quận 10 | `5` | `5` | `5` | **DUNG** | **DUNG** | Khop ky vong |
| 15 | Tìm sân quanh tọa độ này có bãi xe ô tô | `2,7` | `2` | `2,7,6` | **DUNG** | **DUNG** | Khop ky vong |
| 16 | Sân cầu lông Bình Thạnh có thảm xịn | `1,3` | `1` | `1,3` | **DUNG** | **DUNG** | Khop ky vong |
| 17 | Đặt sân 8h sáng ngày mốt ở Gò Vấp | `6,4` | `4` | `4,6` | **DUNG** | **DUNG** | Khop ky vong |
| 18 | Tìm sân giá dưới 120k ở Gò Vấp | `6,4` | `4` | `4,6` | **DUNG** | **DUNG** | Khop ky vong |
| 19 | Sân cầu lông gần tôi nhất | `4,6` | `4` | `4,6,2` | **DUNG** | **DUNG** | Khop ky vong |
| 20 | Tìm sân ở quận 10 lúc 18h tối nay | `5` | `5` | `5` | **DUNG** | **DUNG** | Khop ky vong |
| 21 | Cần sân có thảm BWF bãi xe ô tô ở Bình Thạnh | `1` | `1` | `1,3` | **DUNG** | **DUNG** | Khop ky vong |
| 22 | Sân giá sinh viên dưới 100k ở Tân Bình | `2` | `2` | `2` | **DUNG** | **DUNG** | Khop ky vong |
| 23 | Đặt sân 19h - 21h hôm nay ở Bình Thạnh | `1,3` | `1` | `1,3` | **DUNG** | **DUNG** | Khop ky vong |
| 24 | Tìm sân tại Tân Phú có thảm BWF | `7` | `7` | `7` | **DUNG** | **DUNG** | Khop ky vong |
| 25 | Sân cầu lông mở cửa sáng sớm 6h ở quận 10 | `5` | `5` | `5` | **DUNG** | **DUNG** | Khop ky vong |

---

## 3. PHAN TICH NGUYEN NHAN CAC TRUONG HOP CHUA DAT TOP-1

Tat ca 25 tinh huong deu dat Top-1 hoan hao!

