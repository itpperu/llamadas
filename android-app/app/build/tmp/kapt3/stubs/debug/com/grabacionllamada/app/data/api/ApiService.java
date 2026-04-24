package com.grabacionllamada.app.data.api;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u00006\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0018\u0002\n\u0002\u0010$\n\u0002\u0010\u000e\n\u0002\b\u0003\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010\b\n\u0000\n\u0002\u0018\u0002\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0004\bf\u0018\u00002\u00020\u0001J6\u0010\u0002\u001a\u0014\u0012\u0010\u0012\u000e\u0012\u0004\u0012\u00020\u0005\u0012\u0004\u0012\u00020\u00010\u00040\u00032\u0014\b\u0001\u0010\u0006\u001a\u000e\u0012\u0004\u0012\u00020\u0005\u0012\u0004\u0012\u00020\u00050\u0004H\u00a7@\u00a2\u0006\u0002\u0010\u0007J@\u0010\b\u001a\u0019\u0012\u0015\u0012\u0013\u0012\u0004\u0012\u00020\u0005\u0012\t\u0012\u00070\u0001\u00a2\u0006\u0002\b\t0\u00040\u00032\u0019\b\u0001\u0010\u0006\u001a\u0013\u0012\u0004\u0012\u00020\u0005\u0012\t\u0012\u00070\u0001\u00a2\u0006\u0002\b\t0\u0004H\u00a7@\u00a2\u0006\u0002\u0010\u0007JW\u0010\n\u001a\u0019\u0012\u0015\u0012\u0013\u0012\u0004\u0012\u00020\u0005\u0012\t\u0012\u00070\u0001\u00a2\u0006\u0002\b\t0\u00040\u00032\b\b\u0001\u0010\u000b\u001a\u00020\f2\b\b\u0001\u0010\r\u001a\u00020\u000e2\b\b\u0001\u0010\u000f\u001a\u00020\u00102\b\b\u0001\u0010\u0011\u001a\u00020\u00102\b\b\u0001\u0010\u0012\u001a\u00020\u0010H\u00a7@\u00a2\u0006\u0002\u0010\u0013\u00a8\u0006\u0014"}, d2 = {"Lcom/grabacionllamada/app/data/api/ApiService;", "", "login", "Lretrofit2/Response;", "", "", "request", "(Ljava/util/Map;Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "registerCall", "Lkotlin/jvm/JvmSuppressWildcards;", "uploadAudio", "callId", "", "audioFile", "Lokhttp3/MultipartBody$Part;", "audioHash", "Lokhttp3/RequestBody;", "mimeType", "sourceMode", "(ILokhttp3/MultipartBody$Part;Lokhttp3/RequestBody;Lokhttp3/RequestBody;Lokhttp3/RequestBody;Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "app_debug"})
public abstract interface ApiService {
    
    @retrofit2.http.POST(value = "auth/login")
    @org.jetbrains.annotations.Nullable()
    public abstract java.lang.Object login(@retrofit2.http.Body()
    @org.jetbrains.annotations.NotNull()
    java.util.Map<java.lang.String, java.lang.String> request, @org.jetbrains.annotations.NotNull()
    kotlin.coroutines.Continuation<? super retrofit2.Response<java.util.Map<java.lang.String, java.lang.Object>>> $completion);
    
    @retrofit2.http.POST(value = "calls")
    @org.jetbrains.annotations.Nullable()
    public abstract java.lang.Object registerCall(@retrofit2.http.Body()
    @org.jetbrains.annotations.NotNull()
    java.util.Map<java.lang.String, java.lang.Object> request, @org.jetbrains.annotations.NotNull()
    kotlin.coroutines.Continuation<? super retrofit2.Response<java.util.Map<java.lang.String, java.lang.Object>>> $completion);
    
    @retrofit2.http.Multipart()
    @retrofit2.http.POST(value = "calls/{call_id}/audio")
    @org.jetbrains.annotations.Nullable()
    public abstract java.lang.Object uploadAudio(@retrofit2.http.Path(value = "call_id")
    int callId, @retrofit2.http.Part()
    @org.jetbrains.annotations.NotNull()
    okhttp3.MultipartBody.Part audioFile, @retrofit2.http.Part(value = "audio_hash")
    @org.jetbrains.annotations.NotNull()
    okhttp3.RequestBody audioHash, @retrofit2.http.Part(value = "mime_type")
    @org.jetbrains.annotations.NotNull()
    okhttp3.RequestBody mimeType, @retrofit2.http.Part(value = "source_mode")
    @org.jetbrains.annotations.NotNull()
    okhttp3.RequestBody sourceMode, @org.jetbrains.annotations.NotNull()
    kotlin.coroutines.Continuation<? super retrofit2.Response<java.util.Map<java.lang.String, java.lang.Object>>> $completion);
}