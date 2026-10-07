import type { Locale } from '@/composables/useLocale';

/**
 * A small, dependency-free phone mask. It formats live as the guest types,
 * recognising the two country codes this hotel actually seeds and tests
 * against (+1 and +7) with their conventional groupings, and falls back to a
 * generic "+code grouped-in-3s" format for anything else.
 */

const groupNanp = (digits: string): string => {
    const rest = digits.slice(1);
    const area = rest.slice(0, 3);
    const mid = rest.slice(3, 6);
    const last = rest.slice(6, 10);

    let formatted = '+1';

    if (area) {
        formatted += ` (${area}`;
    }

    if (area.length === 3) {
        formatted += ')';
    }

    if (mid) {
        formatted += ` ${mid}`;
    }

    if (last) {
        formatted += `-${last}`;
    }

    return formatted;
};

const groupRussian = (digits: string): string => {
    const rest = digits.slice(1);
    const area = rest.slice(0, 3);
    const p1 = rest.slice(3, 6);
    const p2 = rest.slice(6, 8);
    const p3 = rest.slice(8, 10);

    let formatted = '+7';

    if (area) {
        formatted += ` (${area}`;
    }

    if (area.length === 3) {
        formatted += ')';
    }

    if (p1) {
        formatted += ` ${p1}`;
    }

    if (p2) {
        formatted += `-${p2}`;
    }

    if (p3) {
        formatted += `-${p3}`;
    }

    return formatted;
};

const groupGeneric = (digits: string): string => {
    const code = digits.slice(0, Math.min(3, digits.length));
    const rest = digits.slice(code.length);
    const groups = rest.match(/.{1,3}/g) ?? [];

    return `+${code}${groups.length ? ' ' + groups.join(' ') : ''}`;
};

/**
 * Reformats a phone input value as the guest types. A bare, un-prefixed
 * number is assumed to be in the active locale's default country, purely as
 * a starting point — the guest can always type their own "+" and code.
 */
export function formatPhoneInput(value: string, locale: Locale): string {
    const hasPlus = value.trim().startsWith('+');
    let digits = value.replace(/\D/g, '').slice(0, 15);

    if (!digits) {
        return '';
    }

    if (!hasPlus) {
        digits = (locale === 'ru' ? '7' : '1') + digits;
    }

    if (digits.startsWith('1') && digits.length <= 11) {
        return groupNanp(digits);
    }

    if (digits.startsWith('7') && digits.length <= 11) {
        return groupRussian(digits);
    }

    return groupGeneric(digits);
}

/**
 * Whether a formatted phone value has enough digits to be a real number,
 * so an obviously incomplete lead never reaches the dashboard.
 */
export function isPhoneComplete(value: string): boolean {
    const digits = value.replace(/\D/g, '');

    if (digits.startsWith('1') || digits.startsWith('7')) {
        return digits.length === 11;
    }

    // A generic international number: a country code plus a plausible
    // number of subscriber digits.
    return digits.length >= 8 && digits.length <= 15;
}
