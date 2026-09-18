import './bootstrap';

const tabs = document.querySelectorAll('.tab');
const registerFields = document.querySelectorAll('.register-only');
const loginFields = document.querySelectorAll('.login-only');
const formTitle = document.querySelector('#form-title');
const formSubtitle = document.querySelector('#form-subtitle');
const submitLabel = document.querySelector('#submit-label');

tabs.forEach((tab) => {
	tab.addEventListener('click', () => {
		const isRegister = tab.dataset.mode === 'register';

		tabs.forEach((item) => {
			const active = item === tab;
			item.classList.toggle('is-active', active);
			item.setAttribute('aria-selected', active ? 'true' : 'false');
		});
		registerFields.forEach((field) => { field.hidden = !isRegister; });
		loginFields.forEach((field) => { field.hidden = isRegister; });
		formTitle.textContent = isRegister ? 'Tạo tài khoản' : 'Đăng nhập';
		formSubtitle.textContent = isRegister ? 'Bắt đầu hành trình ăn lành, sống xanh.' : 'Tiếp tục hành trình ăn lành, sống xanh.';
		submitLabel.textContent = isRegister ? 'Đăng ký ngay' : 'Đăng nhập';
	});
});
