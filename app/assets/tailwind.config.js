/**
 * Tailwind — সাইটের CSS আগে থেকে বানানো (CDN থেকে ব্রাউজারে প্রতিবার বানালে মোবাইলে ল্যাগ হয়)।
 * নতুন ক্লাস যোগ করলে আবার বানাতে হবে:  tailwindcss -c app/assets/tailwind.config.js -i app/assets/tailwind.input.css -o app/assets/site.css --minify
 * primary / cream রঙ অ্যাডমিন সেটিংস থেকে আসে — CSS ভেরিয়েবল (--mm-primary / --mm-cream) দিয়ে।
 */
module.exports = {
    content: ['./app/**/*.php', './index.php'],
    theme: {
        extend: {
            colors: { primary: 'var(--mm-primary)', cream: 'var(--mm-cream)' },
            fontFamily: {
                sans: ['Poppins', '"Hind Siliguri"', 'system-ui', 'sans-serif'],
                serif: ['Sora', '"Hind Siliguri"', 'system-ui', 'sans-serif']
            }
        }
    }
};
