Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('phone')->nullable();
    $table->string('avatar')->nullable();
    $table->enum('role', ['customer', 'seller', 'admin'])->default('customer'); // Phân quyền cơ bản
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});