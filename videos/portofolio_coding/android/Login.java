package harus.bisa;

import java.util.ArrayList;

import org.apache.http.NameValuePair;
import org.apache.http.message.BasicNameValuePair;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

public class Login extends Activity implements OnClickListener{
    
	EditText id, password;
	Button btn_login, btn_cancel;
	TextView sign_up;
	Intent i;
	ProgressDialog progressDialog;
	
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.login);
		id = (EditText) findViewById(R.id.txt_id);
		password = (EditText) findViewById(R.id.txt_pass);
		
		
		btn_cancel = (Button) findViewById(R.id.btn_cancel);
		btn_cancel.setOnClickListener(this);
		
		btn_login = (Button) findViewById(R.id.btn_login);
		btn_login.setOnClickListener(new View.OnClickListener() {		
			public void onClick(View v) {
				AsynTask at = new AsynTask();
				at.execute();
				}
			});
	}

	public class AsynTask extends AsyncTask <String, String, String>{

		protected void onPreExecute() {
	        super.onPreExecute();
	        progressDialog= new ProgressDialog(Login.this);
	        progressDialog.setMessage("Please wait ..");
	        progressDialog.setIndeterminate(false);
	        progressDialog.setCancelable(true);
	        progressDialog.show();
	    }
		
		@Override
		protected String doInBackground(String... arg0) {
			// TODO Auto-generated method stub
			ArrayList<NameValuePair> info_input = new ArrayList<NameValuePair>();
			info_input.add(new BasicNameValuePair("id", id.getText().toString()));
			info_input.add(new BasicNameValuePair("password", password.getText().toString()));      
			String response = null;
			try {
				
				response = Connect.executeHttpPost("http://192.168.1.73:8080/chat/login.php", info_input);
				
				} catch (Exception e) {
					e.printStackTrace();
					}
			return response;
		}
		
		@Override
		protected void onPostExecute(String response){
			String res = response.toString();
			res = res.trim();
			res = res.replaceAll("\\s+", "");
			if (res.equals("1")) {
				progressDialog.dismiss();
				Toast.makeText(Login.this, "Welcome My Chat", Toast.LENGTH_SHORT).show();
				Intent intent = new Intent(Login.this, ListAwal.class); 
				intent.putExtra("id", id.getText().toString());
            	startActivity(intent);
			}else if(res.equals("2")){
				progressDialog.dismiss();
				Toast.makeText(Login.this, "Welcome Admin", Toast.LENGTH_SHORT).show();
				
			} else { 
				progressDialog.dismiss();
				Toast.makeText(Login.this, "Username or Password is incorrect", Toast.LENGTH_SHORT).show();
					}
			}
	}	
	
	public void onClick(View v) {
		// TODO Auto-generated method stub
		switch (v.getId()) {
	
		 case R.id.btn_cancel:
			 AlertDialog.Builder adb = new AlertDialog.Builder(Login.this);
	        	adb.setTitle("Quit Application");
	        	adb.setMessage("Are you sure to quit ?");
	        	adb.setCancelable(false);
	        	
	        	adb.setPositiveButton("Yes", new DialogInterface.OnClickListener() {
	    			public void onClick(DialogInterface dialog, int which) {
						finish();
	    			}
	    		});
	        	
	        	adb.setNegativeButton("No", null);
	    		adb.show();
		}
	}
	
}