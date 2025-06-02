export function getImageUrl(apiUrl, image) {
    let path = 'storage';

    return `${apiUrl}/${path}/${image}`;
}
