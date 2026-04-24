package com.grabacionllamada.app.data.local;

import android.database.Cursor;
import android.os.CancellationSignal;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.room.CoroutinesRoom;
import androidx.room.EntityDeletionOrUpdateAdapter;
import androidx.room.EntityInsertionAdapter;
import androidx.room.RoomDatabase;
import androidx.room.RoomSQLiteQuery;
import androidx.room.util.CursorUtil;
import androidx.room.util.DBUtil;
import androidx.sqlite.db.SupportSQLiteStatement;
import java.lang.Class;
import java.lang.Exception;
import java.lang.Integer;
import java.lang.Long;
import java.lang.Object;
import java.lang.Override;
import java.lang.String;
import java.lang.SuppressWarnings;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.concurrent.Callable;
import javax.annotation.processing.Generated;
import kotlin.Unit;
import kotlin.coroutines.Continuation;

@Generated("androidx.room.RoomProcessor")
@SuppressWarnings({"unchecked", "deprecation"})
public final class CallDao_Impl implements CallDao {
  private final RoomDatabase __db;

  private final EntityInsertionAdapter<CallEntity> __insertionAdapterOfCallEntity;

  private final EntityDeletionOrUpdateAdapter<CallEntity> __updateAdapterOfCallEntity;

  public CallDao_Impl(@NonNull final RoomDatabase __db) {
    this.__db = __db;
    this.__insertionAdapterOfCallEntity = new EntityInsertionAdapter<CallEntity>(__db) {
      @Override
      @NonNull
      protected String createQuery() {
        return "INSERT OR ABORT INTO `calls` (`id`,`telefonoCliente`,`tipo`,`fechaInicio`,`fechaFin`,`duracionSegundos`,`isMetadataSynced`,`isAudioSynced`,`audioPath`,`backendCallId`) VALUES (nullif(?, 0),?,?,?,?,?,?,?,?,?)";
      }

      @Override
      protected void bind(@NonNull final SupportSQLiteStatement statement,
          @NonNull final CallEntity entity) {
        statement.bindLong(1, entity.getId());
        if (entity.getTelefonoCliente() == null) {
          statement.bindNull(2);
        } else {
          statement.bindString(2, entity.getTelefonoCliente());
        }
        if (entity.getTipo() == null) {
          statement.bindNull(3);
        } else {
          statement.bindString(3, entity.getTipo());
        }
        if (entity.getFechaInicio() == null) {
          statement.bindNull(4);
        } else {
          statement.bindString(4, entity.getFechaInicio());
        }
        if (entity.getFechaFin() == null) {
          statement.bindNull(5);
        } else {
          statement.bindString(5, entity.getFechaFin());
        }
        statement.bindLong(6, entity.getDuracionSegundos());
        final int _tmp = entity.isMetadataSynced() ? 1 : 0;
        statement.bindLong(7, _tmp);
        final int _tmp_1 = entity.isAudioSynced() ? 1 : 0;
        statement.bindLong(8, _tmp_1);
        if (entity.getAudioPath() == null) {
          statement.bindNull(9);
        } else {
          statement.bindString(9, entity.getAudioPath());
        }
        if (entity.getBackendCallId() == null) {
          statement.bindNull(10);
        } else {
          statement.bindLong(10, entity.getBackendCallId());
        }
      }
    };
    this.__updateAdapterOfCallEntity = new EntityDeletionOrUpdateAdapter<CallEntity>(__db) {
      @Override
      @NonNull
      protected String createQuery() {
        return "UPDATE OR ABORT `calls` SET `id` = ?,`telefonoCliente` = ?,`tipo` = ?,`fechaInicio` = ?,`fechaFin` = ?,`duracionSegundos` = ?,`isMetadataSynced` = ?,`isAudioSynced` = ?,`audioPath` = ?,`backendCallId` = ? WHERE `id` = ?";
      }

      @Override
      protected void bind(@NonNull final SupportSQLiteStatement statement,
          @NonNull final CallEntity entity) {
        statement.bindLong(1, entity.getId());
        if (entity.getTelefonoCliente() == null) {
          statement.bindNull(2);
        } else {
          statement.bindString(2, entity.getTelefonoCliente());
        }
        if (entity.getTipo() == null) {
          statement.bindNull(3);
        } else {
          statement.bindString(3, entity.getTipo());
        }
        if (entity.getFechaInicio() == null) {
          statement.bindNull(4);
        } else {
          statement.bindString(4, entity.getFechaInicio());
        }
        if (entity.getFechaFin() == null) {
          statement.bindNull(5);
        } else {
          statement.bindString(5, entity.getFechaFin());
        }
        statement.bindLong(6, entity.getDuracionSegundos());
        final int _tmp = entity.isMetadataSynced() ? 1 : 0;
        statement.bindLong(7, _tmp);
        final int _tmp_1 = entity.isAudioSynced() ? 1 : 0;
        statement.bindLong(8, _tmp_1);
        if (entity.getAudioPath() == null) {
          statement.bindNull(9);
        } else {
          statement.bindString(9, entity.getAudioPath());
        }
        if (entity.getBackendCallId() == null) {
          statement.bindNull(10);
        } else {
          statement.bindLong(10, entity.getBackendCallId());
        }
        statement.bindLong(11, entity.getId());
      }
    };
  }

  @Override
  public Object insertCall(final CallEntity call, final Continuation<? super Long> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Long>() {
      @Override
      @NonNull
      public Long call() throws Exception {
        __db.beginTransaction();
        try {
          final Long _result = __insertionAdapterOfCallEntity.insertAndReturnId(call);
          __db.setTransactionSuccessful();
          return _result;
        } finally {
          __db.endTransaction();
        }
      }
    }, $completion);
  }

  @Override
  public Object updateCall(final CallEntity call, final Continuation<? super Unit> $completion) {
    return CoroutinesRoom.execute(__db, true, new Callable<Unit>() {
      @Override
      @NonNull
      public Unit call() throws Exception {
        __db.beginTransaction();
        try {
          __updateAdapterOfCallEntity.handle(call);
          __db.setTransactionSuccessful();
          return Unit.INSTANCE;
        } finally {
          __db.endTransaction();
        }
      }
    }, $completion);
  }

  @Override
  public Object getUnsyncedMetadataCalls(final Continuation<? super List<CallEntity>> $completion) {
    final String _sql = "SELECT * FROM calls WHERE isMetadataSynced = 0";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    final CancellationSignal _cancellationSignal = DBUtil.createCancellationSignal();
    return CoroutinesRoom.execute(__db, false, _cancellationSignal, new Callable<List<CallEntity>>() {
      @Override
      @NonNull
      public List<CallEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfTelefonoCliente = CursorUtil.getColumnIndexOrThrow(_cursor, "telefonoCliente");
          final int _cursorIndexOfTipo = CursorUtil.getColumnIndexOrThrow(_cursor, "tipo");
          final int _cursorIndexOfFechaInicio = CursorUtil.getColumnIndexOrThrow(_cursor, "fechaInicio");
          final int _cursorIndexOfFechaFin = CursorUtil.getColumnIndexOrThrow(_cursor, "fechaFin");
          final int _cursorIndexOfDuracionSegundos = CursorUtil.getColumnIndexOrThrow(_cursor, "duracionSegundos");
          final int _cursorIndexOfIsMetadataSynced = CursorUtil.getColumnIndexOrThrow(_cursor, "isMetadataSynced");
          final int _cursorIndexOfIsAudioSynced = CursorUtil.getColumnIndexOrThrow(_cursor, "isAudioSynced");
          final int _cursorIndexOfAudioPath = CursorUtil.getColumnIndexOrThrow(_cursor, "audioPath");
          final int _cursorIndexOfBackendCallId = CursorUtil.getColumnIndexOrThrow(_cursor, "backendCallId");
          final List<CallEntity> _result = new ArrayList<CallEntity>(_cursor.getCount());
          while (_cursor.moveToNext()) {
            final CallEntity _item;
            final int _tmpId;
            _tmpId = _cursor.getInt(_cursorIndexOfId);
            final String _tmpTelefonoCliente;
            if (_cursor.isNull(_cursorIndexOfTelefonoCliente)) {
              _tmpTelefonoCliente = null;
            } else {
              _tmpTelefonoCliente = _cursor.getString(_cursorIndexOfTelefonoCliente);
            }
            final String _tmpTipo;
            if (_cursor.isNull(_cursorIndexOfTipo)) {
              _tmpTipo = null;
            } else {
              _tmpTipo = _cursor.getString(_cursorIndexOfTipo);
            }
            final String _tmpFechaInicio;
            if (_cursor.isNull(_cursorIndexOfFechaInicio)) {
              _tmpFechaInicio = null;
            } else {
              _tmpFechaInicio = _cursor.getString(_cursorIndexOfFechaInicio);
            }
            final String _tmpFechaFin;
            if (_cursor.isNull(_cursorIndexOfFechaFin)) {
              _tmpFechaFin = null;
            } else {
              _tmpFechaFin = _cursor.getString(_cursorIndexOfFechaFin);
            }
            final int _tmpDuracionSegundos;
            _tmpDuracionSegundos = _cursor.getInt(_cursorIndexOfDuracionSegundos);
            final boolean _tmpIsMetadataSynced;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsMetadataSynced);
            _tmpIsMetadataSynced = _tmp != 0;
            final boolean _tmpIsAudioSynced;
            final int _tmp_1;
            _tmp_1 = _cursor.getInt(_cursorIndexOfIsAudioSynced);
            _tmpIsAudioSynced = _tmp_1 != 0;
            final String _tmpAudioPath;
            if (_cursor.isNull(_cursorIndexOfAudioPath)) {
              _tmpAudioPath = null;
            } else {
              _tmpAudioPath = _cursor.getString(_cursorIndexOfAudioPath);
            }
            final Integer _tmpBackendCallId;
            if (_cursor.isNull(_cursorIndexOfBackendCallId)) {
              _tmpBackendCallId = null;
            } else {
              _tmpBackendCallId = _cursor.getInt(_cursorIndexOfBackendCallId);
            }
            _item = new CallEntity(_tmpId,_tmpTelefonoCliente,_tmpTipo,_tmpFechaInicio,_tmpFechaFin,_tmpDuracionSegundos,_tmpIsMetadataSynced,_tmpIsAudioSynced,_tmpAudioPath,_tmpBackendCallId);
            _result.add(_item);
          }
          return _result;
        } finally {
          _cursor.close();
          _statement.release();
        }
      }
    }, $completion);
  }

  @Override
  public Object getUnsyncedAudioCalls(final Continuation<? super List<CallEntity>> $completion) {
    final String _sql = "SELECT * FROM calls WHERE isMetadataSynced = 1 AND isAudioSynced = 0 AND audioPath IS NOT NULL";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    final CancellationSignal _cancellationSignal = DBUtil.createCancellationSignal();
    return CoroutinesRoom.execute(__db, false, _cancellationSignal, new Callable<List<CallEntity>>() {
      @Override
      @NonNull
      public List<CallEntity> call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfTelefonoCliente = CursorUtil.getColumnIndexOrThrow(_cursor, "telefonoCliente");
          final int _cursorIndexOfTipo = CursorUtil.getColumnIndexOrThrow(_cursor, "tipo");
          final int _cursorIndexOfFechaInicio = CursorUtil.getColumnIndexOrThrow(_cursor, "fechaInicio");
          final int _cursorIndexOfFechaFin = CursorUtil.getColumnIndexOrThrow(_cursor, "fechaFin");
          final int _cursorIndexOfDuracionSegundos = CursorUtil.getColumnIndexOrThrow(_cursor, "duracionSegundos");
          final int _cursorIndexOfIsMetadataSynced = CursorUtil.getColumnIndexOrThrow(_cursor, "isMetadataSynced");
          final int _cursorIndexOfIsAudioSynced = CursorUtil.getColumnIndexOrThrow(_cursor, "isAudioSynced");
          final int _cursorIndexOfAudioPath = CursorUtil.getColumnIndexOrThrow(_cursor, "audioPath");
          final int _cursorIndexOfBackendCallId = CursorUtil.getColumnIndexOrThrow(_cursor, "backendCallId");
          final List<CallEntity> _result = new ArrayList<CallEntity>(_cursor.getCount());
          while (_cursor.moveToNext()) {
            final CallEntity _item;
            final int _tmpId;
            _tmpId = _cursor.getInt(_cursorIndexOfId);
            final String _tmpTelefonoCliente;
            if (_cursor.isNull(_cursorIndexOfTelefonoCliente)) {
              _tmpTelefonoCliente = null;
            } else {
              _tmpTelefonoCliente = _cursor.getString(_cursorIndexOfTelefonoCliente);
            }
            final String _tmpTipo;
            if (_cursor.isNull(_cursorIndexOfTipo)) {
              _tmpTipo = null;
            } else {
              _tmpTipo = _cursor.getString(_cursorIndexOfTipo);
            }
            final String _tmpFechaInicio;
            if (_cursor.isNull(_cursorIndexOfFechaInicio)) {
              _tmpFechaInicio = null;
            } else {
              _tmpFechaInicio = _cursor.getString(_cursorIndexOfFechaInicio);
            }
            final String _tmpFechaFin;
            if (_cursor.isNull(_cursorIndexOfFechaFin)) {
              _tmpFechaFin = null;
            } else {
              _tmpFechaFin = _cursor.getString(_cursorIndexOfFechaFin);
            }
            final int _tmpDuracionSegundos;
            _tmpDuracionSegundos = _cursor.getInt(_cursorIndexOfDuracionSegundos);
            final boolean _tmpIsMetadataSynced;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsMetadataSynced);
            _tmpIsMetadataSynced = _tmp != 0;
            final boolean _tmpIsAudioSynced;
            final int _tmp_1;
            _tmp_1 = _cursor.getInt(_cursorIndexOfIsAudioSynced);
            _tmpIsAudioSynced = _tmp_1 != 0;
            final String _tmpAudioPath;
            if (_cursor.isNull(_cursorIndexOfAudioPath)) {
              _tmpAudioPath = null;
            } else {
              _tmpAudioPath = _cursor.getString(_cursorIndexOfAudioPath);
            }
            final Integer _tmpBackendCallId;
            if (_cursor.isNull(_cursorIndexOfBackendCallId)) {
              _tmpBackendCallId = null;
            } else {
              _tmpBackendCallId = _cursor.getInt(_cursorIndexOfBackendCallId);
            }
            _item = new CallEntity(_tmpId,_tmpTelefonoCliente,_tmpTipo,_tmpFechaInicio,_tmpFechaFin,_tmpDuracionSegundos,_tmpIsMetadataSynced,_tmpIsAudioSynced,_tmpAudioPath,_tmpBackendCallId);
            _result.add(_item);
          }
          return _result;
        } finally {
          _cursor.close();
          _statement.release();
        }
      }
    }, $completion);
  }

  @Override
  public Object getNextCallNeedingAudio(final Continuation<? super CallEntity> $completion) {
    final String _sql = "SELECT * FROM calls WHERE isMetadataSynced = 1 AND isAudioSynced = 0 AND audioPath IS NULL LIMIT 1";
    final RoomSQLiteQuery _statement = RoomSQLiteQuery.acquire(_sql, 0);
    final CancellationSignal _cancellationSignal = DBUtil.createCancellationSignal();
    return CoroutinesRoom.execute(__db, false, _cancellationSignal, new Callable<CallEntity>() {
      @Override
      @Nullable
      public CallEntity call() throws Exception {
        final Cursor _cursor = DBUtil.query(__db, _statement, false, null);
        try {
          final int _cursorIndexOfId = CursorUtil.getColumnIndexOrThrow(_cursor, "id");
          final int _cursorIndexOfTelefonoCliente = CursorUtil.getColumnIndexOrThrow(_cursor, "telefonoCliente");
          final int _cursorIndexOfTipo = CursorUtil.getColumnIndexOrThrow(_cursor, "tipo");
          final int _cursorIndexOfFechaInicio = CursorUtil.getColumnIndexOrThrow(_cursor, "fechaInicio");
          final int _cursorIndexOfFechaFin = CursorUtil.getColumnIndexOrThrow(_cursor, "fechaFin");
          final int _cursorIndexOfDuracionSegundos = CursorUtil.getColumnIndexOrThrow(_cursor, "duracionSegundos");
          final int _cursorIndexOfIsMetadataSynced = CursorUtil.getColumnIndexOrThrow(_cursor, "isMetadataSynced");
          final int _cursorIndexOfIsAudioSynced = CursorUtil.getColumnIndexOrThrow(_cursor, "isAudioSynced");
          final int _cursorIndexOfAudioPath = CursorUtil.getColumnIndexOrThrow(_cursor, "audioPath");
          final int _cursorIndexOfBackendCallId = CursorUtil.getColumnIndexOrThrow(_cursor, "backendCallId");
          final CallEntity _result;
          if (_cursor.moveToFirst()) {
            final int _tmpId;
            _tmpId = _cursor.getInt(_cursorIndexOfId);
            final String _tmpTelefonoCliente;
            if (_cursor.isNull(_cursorIndexOfTelefonoCliente)) {
              _tmpTelefonoCliente = null;
            } else {
              _tmpTelefonoCliente = _cursor.getString(_cursorIndexOfTelefonoCliente);
            }
            final String _tmpTipo;
            if (_cursor.isNull(_cursorIndexOfTipo)) {
              _tmpTipo = null;
            } else {
              _tmpTipo = _cursor.getString(_cursorIndexOfTipo);
            }
            final String _tmpFechaInicio;
            if (_cursor.isNull(_cursorIndexOfFechaInicio)) {
              _tmpFechaInicio = null;
            } else {
              _tmpFechaInicio = _cursor.getString(_cursorIndexOfFechaInicio);
            }
            final String _tmpFechaFin;
            if (_cursor.isNull(_cursorIndexOfFechaFin)) {
              _tmpFechaFin = null;
            } else {
              _tmpFechaFin = _cursor.getString(_cursorIndexOfFechaFin);
            }
            final int _tmpDuracionSegundos;
            _tmpDuracionSegundos = _cursor.getInt(_cursorIndexOfDuracionSegundos);
            final boolean _tmpIsMetadataSynced;
            final int _tmp;
            _tmp = _cursor.getInt(_cursorIndexOfIsMetadataSynced);
            _tmpIsMetadataSynced = _tmp != 0;
            final boolean _tmpIsAudioSynced;
            final int _tmp_1;
            _tmp_1 = _cursor.getInt(_cursorIndexOfIsAudioSynced);
            _tmpIsAudioSynced = _tmp_1 != 0;
            final String _tmpAudioPath;
            if (_cursor.isNull(_cursorIndexOfAudioPath)) {
              _tmpAudioPath = null;
            } else {
              _tmpAudioPath = _cursor.getString(_cursorIndexOfAudioPath);
            }
            final Integer _tmpBackendCallId;
            if (_cursor.isNull(_cursorIndexOfBackendCallId)) {
              _tmpBackendCallId = null;
            } else {
              _tmpBackendCallId = _cursor.getInt(_cursorIndexOfBackendCallId);
            }
            _result = new CallEntity(_tmpId,_tmpTelefonoCliente,_tmpTipo,_tmpFechaInicio,_tmpFechaFin,_tmpDuracionSegundos,_tmpIsMetadataSynced,_tmpIsAudioSynced,_tmpAudioPath,_tmpBackendCallId);
          } else {
            _result = null;
          }
          return _result;
        } finally {
          _cursor.close();
          _statement.release();
        }
      }
    }, $completion);
  }

  @NonNull
  public static List<Class<?>> getRequiredConverters() {
    return Collections.emptyList();
  }
}
