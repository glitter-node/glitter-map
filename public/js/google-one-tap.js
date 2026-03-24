function handleGoogleCredential(response) {
    fetch('/auth/google/one-tap', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            credential: response.credential,
        }),
    }).then((res) => {
        if (res.ok) {
            window.location.href = '/restaurants';
        }
    });
}

window.handleGoogleCredential = handleGoogleCredential;
