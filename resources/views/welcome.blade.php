<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Mộc Nông') }} | Nông sản sạch</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <main class="auth-page">
            <section class="brand-panel" aria-label="Giới thiệu Mộc Nông">
                <div class="brand-mark"><span>MN</span><i></i></div>
                <p class="eyebrow">TỪ ĐẤT LÀNH, ĐẾN BÀN ĂN</p>
                <h1>Vị tươi lành<br><em>mỗi ngày.</em></h1>
                <p class="brand-copy">Kết nối bạn với những sản phẩm nông nghiệp tử tế, rõ nguồn gốc và được tuyển chọn từ các vùng quê Việt.</p>
                <div class="fresh-note"><span class="note-dot"></span><span><strong>100% tươi mới</strong><br>Giao tận tay trong ngày</span></div>
                <div class="leaf leaf-one">✦</div>
                <div class="leaf leaf-two">❋</div>
            </section>

            <section class="form-panel">
                <div class="form-wrap">
                    <div class="mobile-brand"><div class="brand-mark"><span>MN</span><i></i></div><strong>Mộc Nông</strong></div>
                    <div class="form-heading">
                        <p class="eyebrow">CHÀO MỪNG BẠN</p>
                        <h2 id="form-title">Đăng nhập</h2>
                        <p id="form-subtitle">Tiếp tục hành trình ăn lành, sống xanh.</p>
                    </div>

                    <div class="auth-tabs" role="tablist" aria-label="Tài khoản">
                        <button class="tab is-active" type="button" data-mode="login" role="tab" aria-selected="true">Đăng nhập</button>
                        <button class="tab" type="button" data-mode="register" role="tab" aria-selected="false">Đăng ký</button>
                    </div>

                    <form id="auth-form" action="#" method="post">
                        @csrf
                        <div class="field register-only" hidden>
                            <label for="name">Họ và tên</label>
                            <input id="name" name="name" type="text" placeholder="Nguyễn Minh Anh" autocomplete="name">
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" placeholder="ban@example.com" autocomplete="email" required>
                        </div>
                        <div class="field">
                            <div class="field-label"><label for="password">Mật khẩu</label><a href="#" class="login-only">Quên mật khẩu?</a></div>
                            <input id="password" name="password" type="password" placeholder="Tối thiểu 8 ký tự" autocomplete="current-password" required>
                        </div>
                        <div class="field register-only" hidden>
                            <label for="password-confirmation">Xác nhận mật khẩu</label>
                            <input id="password-confirmation" name="password_confirmation" type="password" placeholder="Nhập lại mật khẩu" autocomplete="new-password">
                        </div>
                        <label class="check-row login-only"><input type="checkbox" name="remember"><span>Ghi nhớ đăng nhập</span></label>
                        <button class="submit-button" type="submit"><span id="submit-label">Đăng nhập</span><span aria-hidden="true">→</span></button>
                    </form>

                    <div class="divider"><span>hoặc tiếp tục với</span></div>
                    <div class="socials"><button type="button" aria-label="Tiếp tục với Google">G</button><button type="button" aria-label="Tiếp tục với Facebook">f</button><button type="button" aria-label="Tiếp tục với Apple">●</button></div>
                    <p class="terms">Bằng việc tiếp tục, bạn đồng ý với <a href="#">Điều khoản</a> và <a href="#">Chính sách bảo mật</a>.</p>
                </div>
            </section>
        </main>
    </body>
</html>
//frontend