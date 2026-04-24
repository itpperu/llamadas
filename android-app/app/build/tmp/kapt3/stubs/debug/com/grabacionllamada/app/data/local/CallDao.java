package com.grabacionllamada.app.data.local;

@kotlin.Metadata(mv = {1, 9, 0}, k = 1, xi = 48, d1 = {"\u0000(\n\u0002\u0018\u0002\n\u0002\u0010\u0000\n\u0000\n\u0002\u0018\u0002\n\u0002\b\u0002\n\u0002\u0010 \n\u0002\b\u0002\n\u0002\u0010\t\n\u0002\b\u0003\n\u0002\u0010\u0002\n\u0000\bg\u0018\u00002\u00020\u0001J\u0010\u0010\u0002\u001a\u0004\u0018\u00010\u0003H\u00a7@\u00a2\u0006\u0002\u0010\u0004J\u0014\u0010\u0005\u001a\b\u0012\u0004\u0012\u00020\u00030\u0006H\u00a7@\u00a2\u0006\u0002\u0010\u0004J\u0014\u0010\u0007\u001a\b\u0012\u0004\u0012\u00020\u00030\u0006H\u00a7@\u00a2\u0006\u0002\u0010\u0004J\u0016\u0010\b\u001a\u00020\t2\u0006\u0010\n\u001a\u00020\u0003H\u00a7@\u00a2\u0006\u0002\u0010\u000bJ\u0016\u0010\f\u001a\u00020\r2\u0006\u0010\n\u001a\u00020\u0003H\u00a7@\u00a2\u0006\u0002\u0010\u000b\u00a8\u0006\u000e"}, d2 = {"Lcom/grabacionllamada/app/data/local/CallDao;", "", "getNextCallNeedingAudio", "Lcom/grabacionllamada/app/data/local/CallEntity;", "(Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "getUnsyncedAudioCalls", "", "getUnsyncedMetadataCalls", "insertCall", "", "call", "(Lcom/grabacionllamada/app/data/local/CallEntity;Lkotlin/coroutines/Continuation;)Ljava/lang/Object;", "updateCall", "", "app_debug"})
@androidx.room.Dao()
public abstract interface CallDao {
    
    @androidx.room.Insert()
    @org.jetbrains.annotations.Nullable()
    public abstract java.lang.Object insertCall(@org.jetbrains.annotations.NotNull()
    com.grabacionllamada.app.data.local.CallEntity call, @org.jetbrains.annotations.NotNull()
    kotlin.coroutines.Continuation<? super java.lang.Long> $completion);
    
    @androidx.room.Update()
    @org.jetbrains.annotations.Nullable()
    public abstract java.lang.Object updateCall(@org.jetbrains.annotations.NotNull()
    com.grabacionllamada.app.data.local.CallEntity call, @org.jetbrains.annotations.NotNull()
    kotlin.coroutines.Continuation<? super kotlin.Unit> $completion);
    
    @androidx.room.Query(value = "SELECT * FROM calls WHERE isMetadataSynced = 0")
    @org.jetbrains.annotations.Nullable()
    public abstract java.lang.Object getUnsyncedMetadataCalls(@org.jetbrains.annotations.NotNull()
    kotlin.coroutines.Continuation<? super java.util.List<com.grabacionllamada.app.data.local.CallEntity>> $completion);
    
    @androidx.room.Query(value = "SELECT * FROM calls WHERE isMetadataSynced = 1 AND isAudioSynced = 0 AND audioPath IS NOT NULL")
    @org.jetbrains.annotations.Nullable()
    public abstract java.lang.Object getUnsyncedAudioCalls(@org.jetbrains.annotations.NotNull()
    kotlin.coroutines.Continuation<? super java.util.List<com.grabacionllamada.app.data.local.CallEntity>> $completion);
    
    @androidx.room.Query(value = "SELECT * FROM calls WHERE isMetadataSynced = 1 AND isAudioSynced = 0 AND audioPath IS NULL LIMIT 1")
    @org.jetbrains.annotations.Nullable()
    public abstract java.lang.Object getNextCallNeedingAudio(@org.jetbrains.annotations.NotNull()
    kotlin.coroutines.Continuation<? super com.grabacionllamada.app.data.local.CallEntity> $completion);
}