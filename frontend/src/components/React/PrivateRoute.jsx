import React, { useEffect, useState } from 'react'

const PrivateRoute = ({children}) => {
    const [isAuthenticated, setIsAuthenticated] = useState(false);

    useEffect(() => {
        if (userStore.tokens?.access_token && userStore.user) {
            setIsAuthenticated(true);
        } else {
            // Si no hay sesión, redirige
            localStorage.removeItem("tokens");
            localStorage.removeItem("user");
            window.location.href = "/login";
        }
    }, []);

    // Mientras se valida, puedes mostrar un loader
    if (!isAuthenticated) {
        return (
            <div className="flex justify-center items-center h-screen">
                <div className="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
            </div>
        );
    }

    return children;
}

export default PrivateRoute
