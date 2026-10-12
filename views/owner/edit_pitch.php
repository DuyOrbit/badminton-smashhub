<div class="max-w-3xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Sửa Thông Tin Sân</h1>
            <p class="mt-2 text-white/60">Cập nhật thông tin chi tiết về sân cầu lông của bạn.</p>
        </div>
        <a href="index.php?action=owner_pitches" class="bg-[#0b1116]/10 border border-white/20 text-white/80 hover:bg-[#0b1116]/20 px-4 py-2 rounded-xl text-sm font-bold shadow-none transition-colors">Quay Lại</a>
    </div>

    <?php if(isset($_GET['error'])): ?>
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl" role="alert">
        <strong class="font-bold">Lỗi!</strong>
        <span class="block sm:inline">Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.</span>
    </div>
    <?php endif; ?>

    <div class="bg-[#121b21] rounded-2xl shadow-none border border-white/5 overflow-hidden">
        <form action="index.php?action=owner_update_pitch" method="POST" enctype="multipart/form-data" class="p-8">
            <input type="hidden" name="id" value="<?php echo $pitch_data['id']; ?>">
            
            <div class="space-y-6">
                <!-- Tên sân -->
                <div>
                    <label for="name" class="block text-sm font-bold text-white/80 mb-2">Tên sân cầu lông</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($pitch_data['name']); ?>" required class="w-full px-4 py-3 rounded-xl border border-white/20 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                </div>

                <!-- Địa chỉ & Khu vực -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label for="location" class="block text-sm font-bold text-white/80 mb-2">Địa chỉ chi tiết</label>
                        <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($pitch_data['location']); ?>" required class="w-full px-4 py-3 rounded-xl border border-white/20 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-bold text-white/80 mb-2">Tỉnh / Thành phố</label>
                        <select name="city" id="city" required class="w-full px-4 py-3 rounded-xl border border-white/20 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors text-sm">
                            <option value="">-- Chọn Tỉnh/TP --</option>
                        </select>
                    </div>
                    <div>
                        <label for="district" class="block text-sm font-bold text-white/80 mb-2">Quận / Huyện</label>
                        <select name="district" id="district" required class="w-full px-4 py-3 rounded-xl border border-white/20 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors text-sm" disabled>
                            <option value="">-- Chọn Quận/Huyện --</option>
                        </select>
                    </div>
                </div>
                
                <script>
                    const CURRENT_CITY = "<?php echo htmlspecialchars($pitch_data['city'] ?? 'Thành phố Hồ Chí Minh'); ?>";
                    const CURRENT_DISTRICT = "<?php echo htmlspecialchars($pitch_data['district'] ?? ''); ?>";
                </script>

                <!-- Tọa độ Map -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-bold text-white/80">Ghim vị trí trên bản đồ <span class="text-xs font-normal text-white/40">(Kéo thả ghim hoặc click trên bản đồ)</span></label>
                        <button type="button" id="btnGeocode" class="flex items-center gap-2 bg-[#d8ff3e] hover:bg-[#c5ec2e] text-[#0b1116] font-bold text-xs px-4 py-2 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Tìm trên bản đồ
                        </button>
                    </div>
                    <div id="map" class="w-full h-72 rounded-xl border border-white/20 z-0"></div>
                    <input type="hidden" name="latitude" id="latitude" value="<?php echo htmlspecialchars($pitch_data['latitude'] ?? ''); ?>">
                    <input type="hidden" name="longitude" id="longitude" value="<?php echo htmlspecialchars($pitch_data['longitude'] ?? ''); ?>">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Loại sân -->
                    <div>
                        <label for="type" class="block text-sm font-bold text-white/80 mb-2">Mặt sân / Thảm</label>
                        <select name="type" id="type" class="w-full px-4 py-3 rounded-xl border border-white/20 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors text-sm">
                            <option value="Thảm PVC Tiêu Chuẩn BWF" <?php echo ($pitch_data['type'] == 'Thảm PVC Tiêu Chuẩn BWF') ? 'selected' : ''; ?>>Thảm PVC Tiêu Chuẩn BWF</option>
                            <option value="Thảm PVC Yonex" <?php echo ($pitch_data['type'] == 'Thảm PVC Yonex') ? 'selected' : ''; ?>>Thảm PVC Yonex</option>
                            <option value="Sân Gỗ Thi Đấu" <?php echo ($pitch_data['type'] == 'Sân Gỗ Thi Đấu') ? 'selected' : ''; ?>>Sân Gỗ Thi Đấu</option>
                            <option value="Thảm Alite" <?php echo ($pitch_data['type'] == 'Thảm Alite') ? 'selected' : ''; ?>>Thảm Alite Chống Trượt</option>
                        </select>
                    </div>

                    <!-- Số lượng sân -->
                    <div>
                        <label for="quantity" class="block text-sm font-bold text-white/80 mb-2">Số lượng sân con</label>
                        <input type="number" name="quantity" id="quantity" value="<?php echo htmlspecialchars($pitch_data['quantity'] ?? 1); ?>" min="1" required class="w-full px-4 py-3 rounded-xl border border-white/20 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                    </div>

                    <!-- Giá thuê -->
                    <div>
                        <label for="price_per_hour" class="block text-sm font-bold text-white/80 mb-2">Giá thuê (VNĐ/giờ)</label>
                        <input type="number" name="price_per_hour" id="price_per_hour" value="<?php echo htmlspecialchars($pitch_data['price_per_hour']); ?>" required class="w-full px-4 py-3 rounded-xl border border-white/20 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                    </div>
                </div>

                <!-- Hình ảnh: URL hoặc Tải lên -->
                <div class="bg-[#0b1116] p-6 rounded-xl border border-white/10">
                    <h3 class="text-sm font-bold text-white/80 mb-4">Hình ảnh sân</h3>
                    
                    <?php if($pitch_data['image_url']): ?>
                    <div class="mb-4">
                        <p class="text-sm text-white/70 mb-2 font-medium">Ảnh hiện tại:</p>
                        <img src="<?php echo htmlspecialchars($pitch_data['image_url']); ?>" class="h-32 rounded-lg border border-white/10 object-cover" alt="Current image">
                    </div>
                    <?php endif; ?>

                    <p class="text-xs text-white/60 mb-4 italic">* Nếu không muốn đổi ảnh, vui lòng để trống phần này.</p>

                    <div class="mb-4">
                        <label for="image_url" class="block text-sm font-medium text-white/70 mb-2">Cách 1: Nhập đường dẫn ảnh mới (URL)</label>
                        <input type="url" name="image_url" id="image_url" class="w-full px-4 py-2 rounded-lg border border-white/20 focus:ring-2 focus:ring-green-500 transition-colors" placeholder="https://example.com/new-image.jpg">
                    </div>

                    <div class="relative flex items-center py-2">
                        <div class="flex-grow border-t border-white/20"></div>
                        <span class="flex-shrink-0 mx-4 text-white/40 text-sm font-medium">HOẶC</span>
                        <div class="flex-grow border-t border-white/20"></div>
                    </div>

                    <div class="mt-4">
                        <label for="image_file" class="block text-sm font-medium text-white/70 mb-2">Cách 2: Tải ảnh mới lên từ máy tính</label>
                        <input type="file" name="image_file" id="image_file" accept="image/*" class="w-full px-4 py-2 rounded-lg border border-white/20 focus:ring-2 focus:ring-green-500 transition-colors bg-[#121b21] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-500/20 file:text-green-700 hover:file:bg-green-100">
                    </div>
                </div>

                <!-- Mô tả chi tiết -->
                <div>
                    <label for="description" class="block text-sm font-bold text-white/80 mb-2">Mô tả chi tiết</label>
                    <textarea name="description" id="description" rows="4" class="w-full px-4 py-3 rounded-xl border border-white/20 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"><?php echo htmlspecialchars($pitch_data['description']); ?></textarea>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-white/5 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-none transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const savedLat = document.getElementById('latitude').value;
        const savedLng = document.getElementById('longitude').value;
        
        let startLat = 10.762622;
        let startLng = 106.660172; 
        
        if (savedLat && savedLng) {
            startLat = parseFloat(savedLat);
            startLng = parseFloat(savedLng);
        }
        
        const map = L.map('map', {
            center: [startLat, startLng],
            zoom: (savedLat && savedLng) ? 16 : 13,
            scrollWheelZoom: false
        });
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        let marker = L.marker([startLat, startLng], {
            draggable: true
        }).addTo(map);

        function updateInputs(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
        }
        
        if (!savedLat || !savedLng) {
            updateInputs(startLat, startLng);
        }

        marker.on('dragend', function() {
            const pos = marker.getLatLng();
            updateInputs(pos.lat, pos.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });

        let provincesData = [];
        const citySelect = document.getElementById('city');
        const districtSelect = document.getElementById('district');

        fetch('https://provinces.open-api.vn/api/?depth=2')
            .then(res => res.json())
            .then(data => {
                provincesData = data;
                data.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.name;
                    opt.text = p.name;
                    if (p.name === CURRENT_CITY || p.name.includes(CURRENT_CITY) || CURRENT_CITY.includes(p.name)) {
                        opt.selected = true;
                    }
                    citySelect.appendChild(opt);
                });
                
                // Trigger change to load districts for current city
                if (citySelect.value) {
                    loadDistricts(citySelect.value, CURRENT_DISTRICT);
                }
            });

        function loadDistricts(cityName, selectedDistrict = '') {
            districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
            const cityData = provincesData.find(p => p.name === cityName);
            if (cityData && cityData.districts) {
                cityData.districts.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.name;
                    opt.text = d.name;
                    if (d.name === selectedDistrict || d.name.includes(selectedDistrict) || selectedDistrict.includes(d.name)) {
                        opt.selected = true;
                    }
                    districtSelect.appendChild(opt);
                });
            }
            districtSelect.disabled = false;
        }

        citySelect.addEventListener('change', function() {
            if (this.value) {
                loadDistricts(this.value);
            } else {
                districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
                districtSelect.disabled = true;
            }
            geocodeAddress();
        });

        districtSelect.addEventListener('change', geocodeAddress);
        document.getElementById('location').addEventListener('change', geocodeAddress);

        const delay = ms => new Promise(res => setTimeout(res, ms));

        async function geocodeAddress() {
            const btn = document.getElementById('btnGeocode');
            const locationStr = document.getElementById('location').value.trim();
            const districtStr = districtSelect.value;
            const cityStr = citySelect.value;
            
            if (!cityStr && !locationStr) {
                alert('Vui lòng nhập địa chỉ hoặc chọn Tỉnh/TP trước.');
                return;
            }

            btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Đang tìm...';
            btn.disabled = true;

            const queries = [];
            if (locationStr && districtStr && cityStr) {
                queries.push(locationStr + ', ' + districtStr + ', ' + cityStr);
                queries.push(locationStr + ', ' + cityStr);
            } else if (locationStr && cityStr) {
                queries.push(locationStr + ', ' + cityStr);
            } else if (locationStr) {
                queries.push(locationStr);
            }
            if (districtStr && cityStr) {
                queries.push(districtStr + ', ' + cityStr);
            } else if (cityStr) {
                queries.push(cityStr);
            }

            let found = false;
            let firstRequest = true;
            for (const q of queries) {
                if (found) break;
                if (!firstRequest) await delay(1000);
                firstRequest = false;
                try {
                    const response = await fetch('api_geocode.php?q=' + encodeURIComponent(q));
                    const data = await response.json();
                    
                    if (Array.isArray(data) && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        
                        map.flyTo([lat, lng], 16);
                        marker.setLatLng([lat, lng]);
                        updateInputs(lat, lng);
                        found = true;
                    }
                } catch (err) {
                    console.error("Geocoding error: ", err);
                }
            }

            if (!found) {
                alert('Không tìm thấy địa chỉ "' + locationStr + '". Bạn có thể click trực tiếp trên bản đồ để ghim vị trí.');
            }

            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> Tìm trên bản đồ';
            btn.disabled = false;
        }

        document.getElementById('btnGeocode').addEventListener('click', geocodeAddress);
    });
</script>



