export function getImageUrl(apiUrl, category, sub, image) {
    let path = 'storage/assets/images';

    if (category?.trim()) {
        path += `/${category.trim()}`;
    }

    if (sub?.trim()) {
        path += `/${sub.trim()}`;
    }

    return `${apiUrl}/${path}/${image}`;
}
