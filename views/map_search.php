<!-- LEAFLET.JS CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    /* Custom Map Controls */
    #map { height: 650px; border-radius: 1.5rem; z-index: 10; }
    .custom-user-marker {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .user-pulse {
        width: 22px;
        height: 22px;
        background: #3b82f6;
        border: 3px solid #ffffff;
        border-radius: 50%;
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.7);
        position: relative;
    }
    .user-pulse::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.4);
        animation: pulse-ring 2s infinite ease-out;
    }
    @keyframes pulse-ring {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(3); opacity: 0; }
    }
    .pitch-badge-icon {
        background: #10b981;
        color: white;
        font-weight: 800;
        font-size: 13px;
        padding: 6px 10px;
        border-radius: 12px;
        border: 2px solid white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
        transform: translate(-50%, -50%);
    }
    .pitch-badge-icon:hover {
        background: #059669;
        transform: translate(-50%, -50%) scale(1.08);
        transition: all 0.2s ease;
    }
</style>

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-[#17231d] via-[#10181e] to-[#0b1116] rounded-3xl p-6 md:p-8 text-white shadow-2xl relative overflow-hidden border border-[#d8ff3e]/20">
        <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-[#d8ff3e]/10 blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 bg-[#d8ff3e]/10 text-[#d8ff3e] text-xs font-black px-3 py-1 rounded-full border border-[#d8ff3e]/20 uppercase tracking-wider mb-2">
                    <span>📍</span> HAVERSINE GPS SEARCH
                </span>
                <h1 class="text-2xl md:text-3xl font-black uppercase italic tracking-tight text-white flex items-center gap-2">
                    Bản Đồ Quét Sân Theo Bán Kính
                </h1>
                <p class="text-[#84939b] text-xs sm:text-sm mt-1 max-w-2xl">
                    Kéo thanh trượt bán kính hoặc nhấp bất kỳ điểm nào trên bản đồ để quét toàn bộ các cụm sân cầu lông gần bạn nhất.
                </p>
            </div>

            <!-- Quick stats -->
            <div class="flex items-center gap-4 bg-[#121b21] px-5 py-3.5 rounded-2xl border border-white/10 shadow-lg">
                <div class="text-right">
                    <span class="text-[10px] uppercase text-[#71818a] font-black tracking-wider block">Sân Trong Bán Kính</span>
                    <strong class="text-2xl font-black text-[#d8ff3e]" id="statPitchCount"><?php echo count($pitches); ?> Cụm Sân</strong>
                </div>
                <div class="h-9 w-[1px] bg-[#0b1116]/10"></div>
                <div class="text-left">
                    <span class="text-[10px] uppercase text-[#71818a] font-black tracking-wider block">Bán Kính Quét</span>
                    <strong class="text-2xl font-black text-cyan-400" id="statRadiusVal"><?php echo $radius; ?> KM</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace: Controls + Map + Pitch Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- CỘT TRÁI: ĐIỀU KHIỂN & DANH SÁCH SÂN TRONG BÁN KÍNH (5 Cột) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Bộ lọc bán kính & Vị trí -->
            <div class="bg-[#121b21] rounded-3xl p-6 shadow-xl border border-white/10 space-y-5">
                
                <!-- Thanh trượt Bán kính -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="radiusSlider" class="text-xs font-black text-[#94a3b8] uppercase tracking-wider flex items-center gap-1.5">
                            <span>🎯</span> Bán Kính Quét (Radius):
                        </label>
                        <span class="bg-[#d8ff3e] text-[#0b1116] text-xs font-black px-2.5 py-0.5 rounded-full shadow-sm" id="radiusBadge">
                            <?php echo $radius; ?> km
                        </span>
                    </div>
                    <input type="range" id="radiusSlider" min="1" max="20" step="0.5" value="<?php echo $radius; ?>"
                           class="w-full h-2 bg-[#0b1116] rounded-lg appearance-none cursor-pointer accent-[#d8ff3e]">
                    <div class="flex justify-between text-[11px] text-[#55646d] font-bold mt-1">
                        <span>1 km</span>
                        <span>5 km</span>
                        <span>10 km</span>
                        <span>15 km</span>
                        <span>20 km</span>
                    </div>
                </div>

                <!-- Định vị GPS Của Tôi -->
                <div>
                    <label class="block text-xs font-black text-[#94a3b8] uppercase tracking-wider mb-2">
                        📍 Tâm Vị Trí Của Bạn:
                    </label>
                    <div class="flex gap-2">
                        <button type="button" id="btnGeolocate" 
                                class="flex-1 bg-cyan-500 hover:bg-cyan-400 text-[#0b1116] font-black py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 shadow-[2px_2px_0_#0e7490]">
                            <span>🧭</span> Định Vị Của Tôi
                        </button>
                        <button type="button" id="btnResetCenter"
                                class="px-3 py-2.5 bg-[#0b1116]/5 hover:bg-[#0b1116]/10 text-white font-bold rounded-xl text-xs transition-colors border border-white/10">
                            Về Mặc Định
                        </button>
                    </div>
                </div>

                <!-- Chọn nhanh khu vực mốc -->
                <div>
                    <span class="block text-[10px] font-black text-[#61717b] uppercase tracking-wider mb-1.5">Mốc Khu Vực Nhanh:</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="setCenter(10.812300, 106.711800, 'Bình Thạnh')" 
                                class="px-2.5 py-1 bg-[#0b1116] hover:bg-[#d8ff3e]/10 hover:text-[#d8ff3e] hover:border-[#d8ff3e]/40 border border-white/10 rounded-lg text-xs font-bold text-[#84939b] transition-colors">
                            Bình Thạnh
                        </button>
                        <button type="button" onclick="setCenter(10.798500, 106.649500, 'Tân Bình')" 
                                class="px-2.5 py-1 bg-[#0b1116] hover:bg-[#d8ff3e]/10 hover:text-[#d8ff3e] hover:border-[#d8ff3e]/40 border border-white/10 rounded-lg text-xs font-bold text-[#84939b] transition-colors">
                            Tân Bình
                        </button>
                        <button type="button" onclick="setCenter(10.845200, 106.658700, 'Gò Vấp')" 
                                class="px-2.5 py-1 bg-[#0b1116] hover:bg-[#d8ff3e]/10 hover:text-[#d8ff3e] hover:border-[#d8ff3e]/40 border border-white/10 rounded-lg text-xs font-bold text-[#84939b] transition-colors">
                            Gò Vấp
                        </button>
                        <button type="button" onclick="setCenter(10.776900, 106.700900, 'Quận 1')" 
                                class="px-2.5 py-1 bg-[#0b1116] hover:bg-[#d8ff3e]/10 hover:text-[#d8ff3e] hover:border-[#d8ff3e]/40 border border-white/10 rounded-lg text-xs font-bold text-[#84939b] transition-colors">
                            Quận 1
                        </button>
                    </div>
                </div>

                <!-- Bộ lọc Loại thảm & Giá -->
                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-white/5">
                    <div>
                        <label class="block text-[10px] font-black text-[#71818a] uppercase tracking-wider mb-1">Loại Thảm Sân:</label>
                        <select id="filterType" class="w-full text-xs p-2.5 bg-[#0b1116] border border-white/10 text-white rounded-xl font-medium focus:border-[#d8ff3e] focus:outline-none">
                            <option value="">Tất Cả Loại Thảm</option>
                            <option value="Thảm PVC Tiêu Chuẩn BWF">Thảm BWF Tiêu Chuẩn</option>
                            <option value="Thảm PVC Yonex">Thảm PVC Yonex</option>
                            <option value="Sân Gỗ Thi Đấu">Sân Gỗ Thi Đấu</option>
                            <option value="Thảm Alite">Thảm Alite</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-[#71818a] uppercase tracking-wider mb-1">Giá Tối Đa/Giờ:</label>
                        <select id="filterPrice" class="w-full text-xs p-2.5 bg-[#0b1116] border border-white/10 text-white rounded-xl font-medium focus:border-[#d8ff3e] focus:outline-none">
                            <option value="0">Tất Cả Mức Giá</option>
                            <option value="85000">&le; 85.000đ</option>
                            <option value="95000">&le; 95.000đ</option>
                            <option value="110000">&le; 110.000đ</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- DANH SÁCH SÂN TRONG BÁN KÍNH (Scrollable Cards) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-xs font-black uppercase text-[#94a3b8] tracking-wider">
                        Sân Tìm Thấy (Gần Nhất Trước)
                    </h3>
                    <span class="text-[11px] text-[#71818a]" id="listStatus">Đang cập nhật...</span>
                </div>

                <div id="pitchListContainer" class="space-y-3 max-h-[480px] overflow-y-auto pr-1">
                    <!-- Danh sách sân sẽ được render động bằng JavaScript -->
                </div>
            </div>

        </div>

        <!-- CỘT PHẢI: BẢN ĐỒ LEAFLET TƯƠNG TÁC (7 Cột) -->
        <div class="lg:col-span-7 bg-[#121b21] p-3 rounded-3xl shadow-xl border border-white/10 relative">
            
            <!-- Bản đồ Leaflet Container -->
            <div id="map" class="shadow-inner border border-white/10 rounded-2xl overflow-hidden"></div>

            <!-- Gợi ý tương tác phía dưới bản đồ -->
            <div class="p-3 bg-[#0b1116] rounded-2xl border border-white/5 mt-3 flex items-center justify-between text-xs text-[#84939b]">
                <div class="flex items-center gap-2">
                    <span class="text-base text-cyan-400">💡</span>
                    <span><strong>Mẹo:</strong> Nhấp chuột vào bất kỳ vị trí nào trên bản đồ để đổi tâm quét bán kính mới!</span>
                </div>
                <div class="flex items-center gap-3 text-[11px] font-bold">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Vị trí bạn</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-[#d8ff3e] inline-block"></span> Sân cầu lông</span>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- JAVASCRIPT ĐIỀU KHIỂN BẢN ĐỒ & TÍNH KHOẢNG CÁCH HAVERSINE REALTIME -->
<script>
    // 1. Tọa độ mặc định: Quận Bình Thạnh, TP.HCM
    let currentCenter = [<?php echo $user_lat; ?>, <?php echo $user_lng; ?>];
    let currentRadiusKm = <?php echo $radius; ?>;

    // Dữ liệu sân ban đầu từ PHP
    let pitchesData = <?php echo json_encode($pitches); ?>;

    // 2. Khởi tạo Leaflet Map
    const map = L.map('map', {
        zoomControl: true,
        scrollWheelZoom: true
    }).setView(currentCenter, 13);

    // Sử dụng gạch bản đồ OpenStreetMap chuẩn
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // 3. Khởi tạo Marker người dùng & Vòng tròn bán kính
    const userIcon = L.divIcon({
        className: 'custom-user-marker',
        html: '<div class="user-pulse"></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    let userMarker = L.marker(currentCenter, { icon: userIcon, draggable: true }).addTo(map);
    userMarker.bindTooltip("Vị trí của bạn (Kéo thả để đổi)", { permanent: false, direction: 'top' });

    let radiusCircle = L.circle(currentCenter, {
        color: '#059669',
        fillColor: '#10b981',
        fillOpacity: 0.15,
        weight: 2,
        radius: currentRadiusKm * 1000 // Chuyển từ km sang mét
    }).addTo(map);

    // Nhóm Marker các sân
    let pitchMarkersLayer = L.layerGroup().addTo(map);

    // 4. Hàm vẽ lại các sân trên bản đồ & Danh sách thẻ
    function renderPitches(pitches) {
        pitchMarkersLayer.clearLayers();
        const container = document.getElementById('pitchListContainer');
        container.innerHTML = '';

        document.getElementById('statPitchCount').innerText = `${pitches.length} Cụm Sân`;
        document.getElementById('listStatus').innerText = `Tìm thấy ${pitches.length} sân`;

        if (pitches.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center bg-white/5 rounded-2xl border border-dashed border-gray-300">
                    <span class="text-3xl block mb-2">🏸</span>
                    <strong class="text-white text-sm block">Không tìm thấy sân trong bán kính ${currentRadiusKm} km!</strong>
                    <span class="text-white/60 text-xs mt-1 block">Hãy thử kéo tăng thanh trượt bán kính lên 10km - 15km nhé.</span>
                </div>
            `;
            return;
        }

        pitches.forEach((p, index) => {
            const lat = parseFloat(p.latitude);
            const lng = parseFloat(p.longitude);
            const dist = parseFloat(p.distance).toFixed(1);
            const priceFormatted = new Intl.NumberFormat('vi-VN').format(p.price_per_hour);

            // Tạo Custom Marker cho sân
            const pitchIcon = L.divIcon({
                className: 'custom-pitch-marker',
                html: `<div class="pitch-badge-icon">🏸 ${p.name.substring(0, 18)}...</div>`,
                iconSize: [120, 30]
            });

            const marker = L.marker([lat, lng], { icon: pitchIcon }).addTo(pitchMarkersLayer);

            // Nội dung Popup khi click vào Marker
            const popupContent = `
                <div class="p-1 max-w-xs space-y-2">
                    <img src="${p.image_url}" class="w-full h-28 object-cover rounded-xl mb-1 shadow-sm">
                    <h4 class="font-bold text-sm text-white leading-tight">${p.name}</h4>
                    <p class="text-[11px] text-white/60 leading-tight">${p.location}, ${p.district}</p>
                    <div class="flex items-center justify-between pt-1 border-t border-gray-100 text-xs">
                        <span class="font-extrabold text-emerald-600">${priceFormatted}đ/h</span>
                        <span class="bg-blue-50 text-blue-700 font-bold px-2 py-0.5 rounded text-[10px]">Cách ${dist} km</span>
                    </div>
                    <div class="pt-2 flex gap-1.5">
                        <a href="index.php?action=book&id=${p.id}" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-center py-1.5 rounded-lg text-xs font-bold">Đặt Sân</a>
                        <a href="index.php?action=smart_match&pitch_id=${p.id}" class="px-2 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold" title="Ghép Trận Elo">🎯</a>
                    </div>
                </div>
            `;
            marker.bindPopup(popupContent);

            // Render Card bên cột danh sách (SMASHHUB Dark Theme)
            const card = document.createElement('div');
            card.className = "bg-[#0d151b] p-3.5 rounded-2xl border border-white/10 hover:border-[#d8ff3e]/40 transition-all cursor-pointer hover:shadow-lg group flex gap-3 items-center";
            card.onclick = () => {
                map.flyTo([lat, lng], 15, { duration: 1.2 });
                marker.openPopup();
            };

            card.innerHTML = `
                <img src="${p.image_url}" class="w-16 h-16 rounded-xl object-cover border border-white/10 group-hover:scale-105 transition-transform flex-shrink-0">
                <div class="flex-grow min-w-0">
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="bg-[#0b1116]/5 text-[#94a3b8] text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider border border-white/5">
                            ${p.type}
                        </span>
                        <span class="bg-cyan-500/20 text-cyan-400 text-[10px] font-black px-2 py-0.5 rounded-full flex-shrink-0">
                            Cách ${dist} km
                        </span>
                    </div>
                    <h4 class="font-bold text-white text-xs truncate group-hover:text-[#d8ff3e] transition-colors">${p.name}</h4>
                    <p class="text-[10px] text-[#71818a] truncate mb-2">${p.location}, ${p.district}</p>
                    <div class="flex items-center justify-between">
                        <strong class="text-[#d8ff3e] font-black text-xs">${priceFormatted}đ<span class="text-[9px] font-normal text-[#71818a]">/h</span></strong>
                        <div class="flex items-center gap-1.5">
                            <a href="index.php?action=book&id=${p.id}" class="text-[10px] font-black uppercase bg-[#d8ff3e] hover:bg-[#c6f030] text-[#0b1116] px-2.5 py-1 rounded-lg transition-colors shadow-sm">
                                Đặt
                            </a>
                            <a href="index.php?action=smart_match&pitch_id=${p.id}" class="text-[10px] font-black bg-[#ff7652]/20 text-[#ff7652] hover:bg-[#ff7652]/30 px-2 py-1 rounded-lg transition-colors" title="Lập kèo tại sân này">
                                🎯 Ghép
                            </a>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    }

    // 5. Hàm Fetch dữ liệu theo Bán kính và Tọa độ (AJAX Realtime)
    function fetchPitches() {
        const type = document.getElementById('filterType').value;
        const maxPrice = document.getElementById('filterPrice').value;

        document.getElementById('listStatus').innerText = 'Đang quét bản đồ...';

        const url = `index.php?action=api_pitches_map&lat=${currentCenter[0]}&lng=${currentCenter[1]}&radius=${currentRadiusKm}&type=${encodeURIComponent(type)}&max_price=${maxPrice}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data && data.pitches) {
                    renderPitches(data.pitches);
                }
            })
            .catch(err => {
                console.error("Lỗi fetch sân:", err);
                document.getElementById('listStatus').innerText = 'Lỗi kết nối';
            });
    }

    // 6. Cập nhật vị trí tâm quét mới
    function updateCenter(newLat, newLng) {
        currentCenter = [newLat, newLng];
        userMarker.setLatLng(currentCenter);
        radiusCircle.setLatLng(currentCenter);
        map.panTo(currentCenter);
        fetchPitches();
    }

    // Khi click trực tiếp lên bản đồ -> Đổi vị trí tâm quét ngay lập tức
    map.on('click', function(e) {
        updateCenter(e.latlng.lat, e.latlng.lng);
    });

    // Khi kéo thả Marker người dùng
    userMarker.on('dragend', function(e) {
        const pos = userMarker.getLatLng();
        updateCenter(pos.lat, pos.lng);
    });

    // 7. Lắng nghe sự kiện Thanh trượt Bán kính (Slider)
    const radiusSlider = document.getElementById('radiusSlider');
    radiusSlider.addEventListener('input', function() {
        currentRadiusKm = parseFloat(this.value);
        document.getElementById('radiusBadge').innerText = `${currentRadiusKm} km`;
        document.getElementById('statRadiusVal').innerText = `${currentRadiusKm} KM`;

        // Cập nhật vòng tròn bán kính trên bản đồ
        radiusCircle.setRadius(currentRadiusKm * 1000);
    });

    radiusSlider.addEventListener('change', function() {
        fetchPitches();
    });

    // Bộ lọc loại thảm và giá
    document.getElementById('filterType').addEventListener('change', fetchPitches);
    document.getElementById('filterPrice').addEventListener('change', fetchPitches);

    // 8. Định vị vị trí hiện tại của người dùng qua HTML5 Geolocation
    document.getElementById('btnGeolocate').addEventListener('click', function() {
        if ("geolocation" in navigator) {
            this.innerText = 'Đang định vị...';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.innerHTML = '<span>🧭</span> Định Vị Của Tôi';
                    updateCenter(pos.coords.latitude, pos.coords.longitude);
                    map.setView(currentCenter, 14);
                },
                (err) => {
                    this.innerHTML = '<span>🧭</span> Định Vị Của Tôi';
                    alert("Không thể lấy vị trí GPS từ trình duyệt của bạn (lỗi: " + err.message + "). Vui lòng nhấp trực tiếp lên bản đồ.");
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        } else {
            alert("Trình duyệt không hỗ trợ Geolocation.");
        }
    });

    // Về mặc định
    document.getElementById('btnResetCenter').addEventListener('click', function() {
        setCenter(10.812300, 106.711800, 'Bình Thạnh');
    });

    function setCenter(lat, lng, name) {
        updateCenter(lat, lng);
        map.setView(currentCenter, 13);
    }

    // Khởi chạy render lần đầu
    renderPitches(pitchesData);
</script>
