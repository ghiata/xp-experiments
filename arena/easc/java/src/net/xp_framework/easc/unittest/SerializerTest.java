/* This class is part of the XP framework's EAS connectivity
 *
 * $Id$ 
 */

package net.xp_framework.easc.unittest;

import org.junit.Test;
import net.xp_framework.easc.unittest.Person;
import java.util.HashMap;

import static org.junit.Assert.*;
import static net.xp_framework.easc.protocol.standard.Serializer.*;

public class SerializerTest {

    @Test public void serializeString() throws Exception {
        assertEquals("s:11:\"Hello World\";", serialize("Hello World"));
    }

    @Test public void serializeCharPrimitive() throws Exception {
        assertEquals("s:1:\"X\";", serialize('X'));
    }

    @Test public void serializeCharacter() throws Exception {
        assertEquals("s:1:\"X\";", serialize(new Character('X')));
    }

    @Test public void serializeUmlautCharPrimitive() throws Exception {
        assertEquals("s:1:\"�\";", serialize('�'));
    }

    @Test public void serializeUmlautCharacter() throws Exception {
        assertEquals("s:1:\"�\";", serialize(new Character('�')));
    }

    @Test public void serializeBytePrimitive() throws Exception {
        assertEquals("i:16;", serialize((byte)16));
        assertEquals("i:-16;", serialize((byte)-16));
    }

    @Test public void serializeBytes() throws Exception {
        assertEquals("i:16;", serialize(new Byte((byte)16)));
        assertEquals("i:-16;", serialize(new Byte((byte)-16)));
    }

    @Test public void serializeShortPrimitive() throws Exception {
        assertEquals("i:1214;", serialize((short)1214));
        assertEquals("i:-1214;", serialize((short)-1214));
    }

    @Test public void serializeShorts() throws Exception {
        assertEquals("i:1214;", serialize(new Short((short)1214)));
        assertEquals("i:-1214;", serialize(new Short((short)-1214)));
    }

    @Test public void serializeIntPrimitive() throws Exception {
        assertEquals("i:6100;", serialize(6100));
        assertEquals("i:-6100;", serialize(-6100));
    }

    @Test public void serializeIntegers() throws Exception {
        assertEquals("i:6100;", serialize(new Integer(6100)));
        assertEquals("i:-6100;", serialize(new Integer(-6100)));
    }

    @Test public void serializeLongPrimitive() throws Exception {
        assertEquals("i:6100;", serialize(6100L));
        assertEquals("i:-6100;", serialize(-6100L));
    }

    @Test public void serializeLongs() throws Exception {
        assertEquals("i:6100;", serialize(new Long(6100L)));
        assertEquals("i:-6100;", serialize(new Long(-6100L)));
    }

    @Test public void serializeDoublePrimitive() throws Exception {
        assertEquals("d:0.1;", serialize(0.1));
        assertEquals("d:-0.1;", serialize(-0.1));
    }

    @Test public void serializeDoubles() throws Exception {
        assertEquals("d:0.1;", serialize(new Double(0.1)));
        assertEquals("d:-0.1;", serialize(new Double(-0.1)));
    }

    @Test public void serializeFloatPrimitive() throws Exception {
        assertEquals("d:0.1;", serialize(0.1f));
        assertEquals("d:-0.1;", serialize(-0.1f));
    }

    @Test public void serializeFloats() throws Exception {
        assertEquals("d:0.1;", serialize(new Float(0.1f)));
        assertEquals("d:-0.1;", serialize(new Float(-0.1f)));
    }
    
    @Test public void serializeBooleanPrimitive() throws Exception {
        assertEquals("b:1;", serialize(true));
        assertEquals("b:0;", serialize(false));
    }

    @Test public void serializeBooleans() throws Exception {
        assertEquals("b:1;", serialize(new Boolean(true)));
        assertEquals("b:0;", serialize(new Boolean(false)));
    }

    @Test public void serializeValueObject() throws Exception {
        assertEquals(
            "O:37:\"net.xp_framework.easc.unittest.Person\":2:{s:2:\"id\";i:1549;s:4:\"name\";s:11:\"Timm Friebe\";}", 
            serialize(new Person())
        );
    }

    @Test public void serializeStringHashMap() throws Exception {
        HashMap h= new HashMap();
        h.put("key", "value");
        h.put("number", "6100");
        
        assertEquals(
            "a:2:{s:3:\"key\";s:5:\"value\";s:6:\"number\";s:4:\"6100\";}", 
            serialize(h)
        );
    }

    @Test public void serializeMixedHashMap() throws Exception {
        HashMap h= new HashMap();
        h.put("key", "value");
        h.put("number", 6100);
        
        assertEquals(
            "a:2:{s:3:\"key\";s:5:\"value\";s:6:\"number\";i:6100;}", 
            serialize(h)
        );
    }
    
    @Test public void serializeStringArray() throws Exception {
        assertEquals(
            "a:2:{i:0;s:5:\"First\";i:1;s:6:\"Second\";}", 
            serialize(new String[] { "First", "Second" })
        );
    }
}
