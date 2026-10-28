<?php
/**
 * SiteSettings model (C# SiteSettings.cs এর PHP ভার্সন)
 */
class SiteSettings
{
    public const DEFAULT_TUTOR_IMG = 'https://i.ibb.co.com/Hfpwnx46/111.png';
    public const DEFAULT_LOGO = 'https://i.ibb.co/fd9sY88Y/IMG-6916-1.png';

    public int $Id = 0;
    public string $HeroImageUrls = '';
    public string $TutorSectionImageUrl = self::DEFAULT_TUTOR_IMG;
    public string $SiteName = 'SMART TUTOR HUB';
    public string $NavbarLogoUrl = self::DEFAULT_LOGO;
    public string $FooterLogoUrl = self::DEFAULT_LOGO;
    public int $LogoSizePx = 56;
    public string $LogoShape = 'circle';
    public bool $SliderEnabled = false;
    public int $SliderIntervalSeconds = 5;
    public string $PrimaryColor = '#02B8F5';
    public string $SecondaryColor = '#F5FBFF';
    public string $AccentColor = '#FFB300';
    public string $TextColor = '#334155';
    public string $NavbarBgColor = '#134856';
    public string $FooterBgColor = '#08224A';
    public ?string $CreatedAt = null;
    public ?string $UpdatedAt = null;

    public static function fromRow(?array $r): self
    {
        $s = new self();
        if (!$r) return $s;
        $s->Id = (int)($r['Id'] ?? 0);
        $s->HeroImageUrls = (string)($r['HeroImageUrls'] ?? '');
        $s->TutorSectionImageUrl = !is_blank($r['TutorSectionImageUrl'] ?? null) ? $r['TutorSectionImageUrl'] : self::DEFAULT_TUTOR_IMG;
        if (($r['SiteName'] ?? null) !== null) $s->SiteName = $r['SiteName'];
        if (($r['NavbarLogoUrl'] ?? null) !== null) $s->NavbarLogoUrl = $r['NavbarLogoUrl'];
        if (($r['FooterLogoUrl'] ?? null) !== null) $s->FooterLogoUrl = $r['FooterLogoUrl'];
        if (($r['LogoSizePx'] ?? null) !== null) $s->LogoSizePx = (int)$r['LogoSizePx'];
        if (($r['LogoShape'] ?? null) !== null) $s->LogoShape = $r['LogoShape'];
        $s->SliderEnabled = (bool)($r['SliderEnabled'] ?? false);
        $s->SliderIntervalSeconds = (int)($r['SliderIntervalSeconds'] ?? 5);
        $s->PrimaryColor = $r['PrimaryColor'] ?? '#02B8F5';
        $s->SecondaryColor = $r['SecondaryColor'] ?? '#F5FBFF';
        $s->AccentColor = $r['AccentColor'] ?? '#FFB300';
        $s->TextColor = $r['TextColor'] ?? '#334155';
        $s->NavbarBgColor = $r['NavbarBgColor'] ?? '#134856';
        $s->FooterBgColor = $r['FooterBgColor'] ?? '#08224A';
        // পুরনো সবুজ/ক্রিম ডিফল্ট থিম থাকলে নতুন "মাস্টারমশাই" থিমে বদলে দেওয়া হয়
        $old = ['PrimaryColor' => '#1F3B2C', 'SecondaryColor' => '#F3F1E7', 'AccentColor' => '#F4C744',
                'TextColor' => '#4B5648', 'NavbarBgColor' => '#F3F1E7', 'FooterBgColor' => '#F3F1E7'];
        $new = self::themeDefaults();
        foreach ($old as $k => $v) {
            if (strcasecmp((string)$s->$k, $v) === 0) $s->$k = $new[$k];
        }
        $s->CreatedAt = $r['CreatedAt'] ?? null;
        $s->UpdatedAt = $r['UpdatedAt'] ?? null;
        return $s;
    }

    /** নতুন থিমের ডিফল্ট রং */
    public static function themeDefaults(): array
    {
        return ['PrimaryColor' => '#02B8F5', 'SecondaryColor' => '#F5FBFF', 'AccentColor' => '#FFB300',
                'TextColor' => '#334155', 'NavbarBgColor' => '#134856', 'FooterBgColor' => '#08224A'];
    }

    /** @return string[] */
    public function GetHeroImageList(): array
    {
        if (is_blank($this->HeroImageUrls)) return [];
        return array_values(array_filter(array_map('trim', explode(',', $this->HeroImageUrls)), fn($x) => $x !== ''));
    }

    public function GetLogoBorderRadius(): string
    {
        return match (strtolower($this->LogoShape ?? '')) {
            'circle' => '50%',
            'rounded' => '16px',
            'square' => '0',
            default => '50%',
        };
    }

    public function GetSafeLogoSize(): int
    {
        return max(24, min(100, $this->LogoSizePx));
    }

    public function GetNavbarLogo(): string
    {
        return is_blank($this->NavbarLogoUrl) ? self::DEFAULT_LOGO : $this->NavbarLogoUrl;
    }

    public function GetFooterLogo(): string
    {
        return is_blank($this->FooterLogoUrl) ? self::DEFAULT_LOGO : $this->FooterLogoUrl;
    }

    public function GetSafeSiteName(): string
    {
        return is_blank($this->SiteName) ? 'SMART TUTOR HUB' : trim($this->SiteName);
    }
}
