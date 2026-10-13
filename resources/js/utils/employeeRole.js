// Cermin App\Enums\UserRole::fromJabatan() — urutan aturan harus sama dengan di backend.
const JABATAN_ROLE_RULES = [
    [['ADMIN'], 'admin'],
    [['TRAFFIC'], 'trafficman'],
    [['HELPER'], 'helper'],
    [['DEPT HEAD', 'SECTION HEAD'], 'pimpinan'],
    [['GROUP LEADER', 'GL'], 'group-leader'],
];

export const roleFromJabatan = (jabatan) => {
    const upper = String(jabatan || '').toUpperCase();
    const rule = JABATAN_ROLE_RULES.find(([keywords]) => keywords.some((keyword) => upper.includes(keyword)));

    return rule ? rule[1] : 'operator';
};
