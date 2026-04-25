package com.grabacionllamada.app.utils;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u00004\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0018\u0002\n\u0000\n\u0002\u0010\u0002\n\u0000\n\u0002\u0010\u000e\n\u0002\b\u0003\n\u0002\u0010\b\n\u0000\n\u0002\u0010\u000b\n\u0002\b\b\u0018\u00002\u00020\u0001B\r\u0012\u0006\u0010\u0002\u001a\u00020\u0003\u00a2\u0006\u0002\u0010\u0004J\u0006\u0010\u0007\u001a\u00020\bJ\b\u0010\t\u001a\u0004\u0018\u00010\nJ\b\u0010\u000b\u001a\u0004\u0018\u00010\nJ\u0006\u0010\f\u001a\u00020\nJ\u0006\u0010\r\u001a\u00020\u000eJ\u0006\u0010\u000f\u001a\u00020\u0010J&\u0010\u0011\u001a\u00020\b2\u0006\u0010\u0012\u001a\u00020\n2\u0006\u0010\u0013\u001a\u00020\u000e2\u0006\u0010\u0014\u001a\u00020\n2\u0006\u0010\u0015\u001a\u00020\nJ\u000e\u0010\u0016\u001a\u00020\b2\u0006\u0010\u0017\u001a\u00020\nR\u000e\u0010\u0005\u001a\u00020\u0006X\u0082\u0004\u00a2\u0006\u0002\n\u0000\u00a8\u0006\u0018"}, d2 = {"Lcom/grabacionllamada/app/utils/SessionManager;", "", "context", "Landroid/content/Context;", "(Landroid/content/Context;)V", "prefs", "Landroid/content/SharedPreferences;", "clearSession", "", "getAuthToken", "", "getDeviceUuid", "getServerUrl", "getVendedorId", "", "isLoggedIn", "", "saveAuthData", "token", "vendedorId", "vendedorNombre", "deviceUuid", "saveServerUrl", "url", "app_debug"})
public final class SessionManager {
    @org.jetbrains.annotations.NotNull()
    private final android.content.SharedPreferences prefs = null;
    
    public SessionManager(@org.jetbrains.annotations.NotNull()
    android.content.Context context) {
        super();
    }
    
    public final void saveAuthData(@org.jetbrains.annotations.NotNull()
    java.lang.String token, int vendedorId, @org.jetbrains.annotations.NotNull()
    java.lang.String vendedorNombre, @org.jetbrains.annotations.NotNull()
    java.lang.String deviceUuid) {
    }
    
    @org.jetbrains.annotations.Nullable()
    public final java.lang.String getAuthToken() {
        return null;
    }
    
    public final int getVendedorId() {
        return 0;
    }
    
    @org.jetbrains.annotations.Nullable()
    public final java.lang.String getDeviceUuid() {
        return null;
    }
    
    public final boolean isLoggedIn() {
        return false;
    }
    
    public final void saveServerUrl(@org.jetbrains.annotations.NotNull()
    java.lang.String url) {
    }
    
    @org.jetbrains.annotations.NotNull()
    public final java.lang.String getServerUrl() {
        return null;
    }
    
    public final void clearSession() {
    }
}