<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 h-[calc(100vh-80px)] flex">
    <!-- Sidebar -->
    <div class="w-1/3 bg-[#0b1116] border-r border-white/10 flex flex-col rounded-l-2xl shadow-none">
        <div class="p-4 border-b border-white/5 bg-[#0b1116]/5 flex-shrink-0">
            <h2 class="text-xl font-bold text-white">Đoạn Chat</h2>
            
            <form action="index.php" method="GET" class="mt-3 relative">
                <input type="hidden" name="action" value="chat">
                <input type="text" name="search_phone" placeholder="Tìm bằng số điện thoại..." required
                       class="w-full pl-9 pr-3 py-2 bg-[#0b1116] border border-white/10 rounded-lg text-sm focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition">
                <svg class="w-4 h-4 text-white/40 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </form>
            
            <?php if(isset($search_error)): ?>
                <div class="mt-2 text-xs text-red-500 font-medium"><?php echo $search_error; ?></div>
            <?php endif; ?>
        </div>
        <div class="overflow-y-auto flex-1 p-2 space-y-1">
            <?php if(count($contacts) > 0): ?>
                <?php foreach($contacts as $c): ?>
                    <a href="index.php?action=chat&user=<?php echo $c['contact_id']; ?>" 
                       class="flex items-center p-3 rounded-xl hover:bg-[#0b1116]/5 transition <?php echo (isset($active_user) && $active_user['id'] == $c['contact_id']) ? 'bg-blue-500/10 ring-1 ring-blue-500/30' : ''; ?>">
                        <div class="relative w-12 h-12 rounded-full overflow-hidden bg-[#0b1116]/10 flex-shrink-0">
                            <?php if(!empty($c['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($c['avatar_url']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-full h-full text-white/40" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4 flex-1 min-w-0">
                            <div class="flex justify-between items-baseline mb-0.5">
                                <h4 class="text-sm font-bold text-white truncate"><?php echo htmlspecialchars($c['name']); ?></h4>
                                <span class="text-xs text-white/40 flex-shrink-0"><?php echo date('H:i d/m', strtotime($c['created_at'])); ?></span>
                            </div>
                            <p class="text-xs text-white/60 truncate <?php echo ($c['is_read'] == 0 && $c['sender_id'] != $_SESSION['user_id']) ? 'font-bold text-white' : ''; ?>">
                                <?php echo htmlspecialchars($c['last_message']); ?>
                            </p>
                        </div>
                        <?php if($c['is_read'] == 0 && $c['sender_id'] != $_SESSION['user_id']): ?>
                            <div class="w-3 h-3 bg-blue-500/100 rounded-full ml-2 flex-shrink-0"></div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center p-4 text-white/40 text-sm mt-10">Chưa có cuộc trò chuyện nào.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="w-2/3 bg-[#0b1116]/5 flex flex-col rounded-r-2xl shadow-none border border-l-0 border-white/10 relative">
        <?php if($active_user): ?>
            <!-- Header -->
            <div class="p-4 bg-[#0b1116] border-b border-white/10 flex items-center justify-between shadow-none z-10 absolute top-0 w-full rounded-tr-2xl">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-[#0b1116]/10 flex-shrink-0 border border-white/5">
                        <?php if(!empty($active_user['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($active_user['avatar_url']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <svg class="w-full h-full text-white/40" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-bold text-white leading-tight"><?php echo htmlspecialchars($active_user['name']); ?></h3>
                        <p class="text-xs font-medium text-green-500 flex items-center">
                            <span class="w-1.5 h-1.5 bg-green-500/100/100/100 rounded-full mr-1"></span> Đang trực tuyến
                        </p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="index.php?action=matchmaking" class="text-white/40 hover:text-blue-500 transition p-2 bg-[#0b1116]/5 rounded-full">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </a>
                    <a href="tel:<?php echo htmlspecialchars($active_user['phone']); ?>" class="text-white/40 hover:text-green-500 transition p-2 bg-[#0b1116]/5 rounded-full">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Messages Area (Offset for absolute Header) -->
            <div id="messagesArea" class="flex-1 p-6 overflow-y-auto space-y-4 mt-[72px] mb-[70px]">
                <?php foreach($messages as $m): ?>
                    <?php if($m['sender_id'] == $_SESSION['user_id']): ?>
                        <div class="flex justify-end">
                            <div class="bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 max-w-md shadow-none">
                                <p class="text-sm"><?php echo nl2br(htmlspecialchars($m['content'])); ?></p>
                                <span class="text-[10px] text-blue-200 mt-1 block text-right"><?php echo date('H:i', strtotime($m['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex justify-start">
                            <div class="w-8 h-8 rounded-full overflow-hidden bg-[#0b1116]/10 flex-shrink-0 mt-auto mr-2 border border-white shadow-none">
                                <?php if(!empty($m['avatar_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($m['avatar_url']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <svg class="w-full h-full text-white/40" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <?php endif; ?>
                            </div>
                            <div class="bg-[#0b1116] border border-white/5 text-white rounded-2xl rounded-tl-sm px-4 py-2.5 max-w-md shadow-none">
                                <p class="text-sm"><?php echo nl2br(htmlspecialchars($m['content'])); ?></p>
                                <span class="text-[10px] text-white/40 mt-1 block"><?php echo date('H:i', strtotime($m['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Input Form -->
            <div class="p-3 bg-[#0b1116] border-t border-white/10 absolute bottom-0 w-full rounded-br-2xl">
                <form id="chatForm" class="flex items-end gap-2 relative">
                    <input type="hidden" id="receiverId" value="<?php echo $active_user['id']; ?>">
                    <textarea id="msgContent" rows="1" class="w-full bg-[#0b1116]/10 border-transparent focus:bg-[#0b1116] focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-xl py-3 pl-4 pr-12 resize-none transition-all shadow-inner" placeholder="Nhập tin nhắn..." required></textarea>
                    <button type="submit" class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-none absolute right-2 bottom-2">
                        <svg class="w-5 h-5 transform rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
                    </button>
                </form>
            </div>
            
            <script>
                const currentUserId = <?php echo $_SESSION['user_id']; ?>;
                const messagesArea = document.getElementById('messagesArea');
                
                function scrollToBottom(smooth = false) {
                    if (!messagesArea) return;
                    if (smooth) {
                        messagesArea.scrollTo({ top: messagesArea.scrollHeight, behavior: 'smooth' });
                    } else {
                        messagesArea.scrollTop = messagesArea.scrollHeight;
                    }
                }

                // Auto-scroll on initial render and window loaded
                scrollToBottom();
                window.addEventListener('load', () => scrollToBottom());
                setTimeout(() => scrollToBottom(), 300);

                // Handle sending msg
                document.getElementById('chatForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    let content = document.getElementById('msgContent').value;
                    let receiverId = document.getElementById('receiverId').value;
                    if(!content.trim()) return;

                    let formData = new FormData();
                    formData.append('receiver_id', receiverId);
                    formData.append('content', content);

                    // Add a temporary optimistic message to UI for speed feeling
                    let time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    messagesArea.innerHTML += `
                        <div class="flex justify-end opacity-70" id="temp-msg">
                            <div class="bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 max-w-md shadow-none">
                                <p class="text-sm">${content.replace(/\n/g, '<br>')}</p>
                                <span class="text-[10px] text-blue-200 mt-1 block text-right">${time}</span>
                            </div>
                        </div>
                    `;
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                    document.getElementById('msgContent').value = '';

                    fetch('index.php?action=api_send_message', {
                        method: 'POST',
                        body: formData
                    }).then(res => res.json()).then(data => {
                        if(data.success) {
                            fetchMessages(); // Immediately pull new msg from DB
                        }
                    });
                });

                // Handle pressing Enter to send without shifting line
                document.getElementById('msgContent').addEventListener('keypress', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
                    }
                });

                let lastMsgHtml = messagesArea.innerHTML;

                function escapeHtml(text) {
                    let div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }

                // Polling for new messages
                function fetchMessages() {
                    let receiverId = document.getElementById('receiverId').value;
                    fetch('index.php?action=api_get_messages&user=' + receiverId)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.length >= 0) {
                            let newHtml = '';
                            data.forEach(m => {
                                let timeStr = m.created_at.replace(/-/g, '/'); // iOS fallback
                                let time = new Date(timeStr).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                                let safeContent = escapeHtml(m.content).replace(/\n/g, '<br>');
                                if (m.sender_id == currentUserId) {
                                    newHtml += `
                                        <div class="flex justify-end">
                                            <div class="bg-blue-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 max-w-md shadow-none">
                                                <p class="text-sm">${safeContent}</p>
                                                <span class="text-[10px] text-blue-200 mt-1 block text-right">${time}</span>
                                            </div>
                                        </div>
                                    `;
                                } else {
                                    let avatarHtml = m.avatar_url ? `<img src="${encodeURI(m.avatar_url)}" class="w-full h-full object-cover">` : `<svg class="w-full h-full text-white/40" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"/></svg>`;
                                    newHtml += `
                                        <div class="flex justify-start">
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-[#0b1116]/10 flex-shrink-0 mt-auto mr-2 border border-white shadow-none">
                                                ${avatarHtml}
                                            </div>
                                            <div class="bg-[#0b1116] border border-white/5 text-white rounded-2xl rounded-tl-sm px-4 py-2.5 max-w-md shadow-none">
                                                <p class="text-sm">${safeContent}</p>
                                                <span class="text-[10px] text-white/40 mt-1 block">${time}</span>
                                            </div>
                                        </div>
                                    `;
                                }
                            });
                            
                            // Re-scroll to bottom only if new HTML is longer/different
                            if (newHtml !== lastMsgHtml) {
                                messagesArea.innerHTML = newHtml;
                                messagesArea.scrollTop = messagesArea.scrollHeight;
                                lastMsgHtml = newHtml;
                            }
                        }
                    });
                }

                // Poll every 3 seconds
                setInterval(fetchMessages, 3000);
            </script>

        <?php else: ?>
            <div class="flex flex-col items-center justify-center flex-1 h-full text-center bg-[#0b1116]/5 rounded-r-2xl">
                <svg class="w-24 h-24 text-gray-200 mb-6 drop-shadow-none" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path></svg>
                <h3 class="text-2xl font-extrabold text-white tracking-tight">Cùng kết bạn và chốt kèo</h3>
                <p class="text-white/60 mt-3 max-w-md text-sm leading-relaxed">Chọn một cuộc trò chuyện ở danh sách bên trái hoặc bắt đầu giao lưu trực tiếp với các đối thủ từ trang Bảng Tìm Đối.</p>
                <a href="index.php?action=matchmaking" class="mt-8 inline-flex items-center px-8 py-3.5 border border-transparent text-sm font-bold rounded-xl shadow-lg shadow-blue-200 text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 transition-all transform hover:-translate-y-0.5">
                    Đến Bảng Cáp Kèo
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
