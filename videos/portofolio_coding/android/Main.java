package harus.bisa;

import android.app.Activity;
import android.content.Intent;
import android.graphics.Typeface;
import android.os.Bundle;
import android.os.Handler;
import android.widget.ImageView;
import android.widget.TextView;

public class Main extends Activity {
	TextView mymichatawal, keep;
	ImageView logo1, logo2, logo3;
	
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.main);
		
		mymichatawal = (TextView) findViewById(R.id.mymichatawal);
        keep = (TextView) findViewById(R.id.keep_con);
        logo1 = (ImageView) findViewById(R.id.logo1);
        logo2 = (ImageView) findViewById(R.id.logo2);
        logo3 = (ImageView) findViewById(R.id.logo3);
        logo1.setColorFilter(R.color.orange);
        logo2.setColorFilter(R.color.orange);
        logo3.setColorFilter(R.color.orange);
        
        String fontPath = "fonts/Airmole Shaded.ttf";
        Typeface tf = Typeface.createFromAsset(getAssets(), fontPath);
        mymichatawal.setTypeface(tf);
        
        String fontPath2 = "fonts/BethHand Regular.ttf";
        Typeface tf2 = Typeface.createFromAsset(getAssets(), fontPath2);
        keep.setTypeface(tf2);
       
        final Handler handler = new Handler();
	        handler.postDelayed(new Runnable() {
	            public void run() {
	                Intent i = new Intent(Main.this, Login.class);
	                Main.this.startActivity(i);
	                Main.this.finish();
	            }
	        }, 7000);
	
		
	
		final Handler handlera = new Handler();
	    handlera.postDelayed(new Runnable() {
	        public void run() {
	            logo1.setColorFilter(null);
	        }
	    }, 2000);
	    
	    final Handler handlerb = new Handler();
	    handlerb.postDelayed(new Runnable() {
	        public void run() {
	            logo2.setColorFilter(null);
	        }
	    }, 4000);
	    
	    final Handler handlerc = new Handler();
	    handlerc.postDelayed(new Runnable() {
	        public void run() {
	            logo3.setColorFilter(null);
	            
	        }
	    }, 6000);

}
	

}
