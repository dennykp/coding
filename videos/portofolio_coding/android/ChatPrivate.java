package harus.bisa;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.HashMap;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import android.app.ListActivity;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.ListAdapter;
import android.widget.SimpleAdapter;
import android.widget.TextView;
import android.widget.Toast;

public class ChatPrivate extends ListActivity{
	private EditText input_chat;
	private Button btn_send;
	private String send_msg, msg;
	private TextView to_nama;
	String url = "http://192.168.1.73:8080/chat/send_chat_private.php";
	String ID_USER, ID, nama;
	ImageButton btn_profile;
	
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.chat_private);	
		
		Intent in = getIntent();
		ID_USER = in.getStringExtra("id_user");
		ID = in.getStringExtra("id");
		nama = in.getStringExtra("to_nama");
		
		new getChatPrivate (this, null).start();
		
		input_chat = (EditText) findViewById(R.id.txt_in_chat);
		
		btn_send = (Button) findViewById(R.id.btn_send);
		btn_send.setOnClickListener(new Button.OnClickListener(){
			public void onClick(View v) {
				SendChatP task = new SendChatP();
				task.execute();
			}
			});
		
		btn_profile = (ImageButton) findViewById(R.id.btn_profile);
		btn_profile.setOnClickListener(new View.OnClickListener() {		
			public void onClick(View v) {
				
				}
			});
		
		
		}
	
	class SendChatP extends AsyncTask<String, Integer, String>{

		@Override
		protected String doInBackground(String... arg0) {
			
			// TODO Auto-generated method stub
			try {
				msg = input_chat.getText().toString();
				send_msg = msg.replace(' ', '~');
				String dari = getIntent().getStringExtra("id");
				String ke = getIntent().getStringExtra("id_user");
			
				url += "?dari="+dari+"&ke="+ke+"&msg="+send_msg;
				
				send_msg = "";
				
				getRequest(url);
			} catch (Exception e) {
				//TODO Auto-generated catch block
				e.printStackTrace();
			}
			return null;
		}
		
		protected void onPostExecute(String result){
			//chatArray.add("Me : " + msg);
			input_chat.setText("");
		}
	}
	
		public void getRequest(String Url) {
			
			HttpClient client = new DefaultHttpClient();
			HttpGet request = new HttpGet(url);
			try {
				client.execute(request);
				//Toast.makeText(this, "Sukses Tambah Data " + request(response) + " ", Toast.LENGTH_SHORT).show();
				
			} catch (Exception ex) {
				Toast.makeText(this, "Please check your connection", Toast.LENGTH_SHORT).show();
			}
		}

		public static String request(HttpResponse response) {
		String result = "";
		try {
			InputStream in = response.getEntity().getContent();
			BufferedReader reader = new BufferedReader(new InputStreamReader(in));
			StringBuilder str = new StringBuilder();
			String line = null;
			while((line = reader.readLine()) != null) {
				str.append(line + "\n");
			}
			in.close();
			result = str.toString();
		} catch (Exception ex) {
			result = "Error";
		}
		return result;
	}
	
	
		class showChatList extends AsyncTask<String, Integer, ArrayList<HashMap<String, String>> > {
			
			ArrayList<HashMap<String, String>> mylist = new ArrayList<HashMap<String, String>>();

			@Override
			protected ArrayList<HashMap<String, String>> doInBackground(String... params) {
				// TODO Auto-generated method stub
				try {
					
					JSONObject json = JSONParser.getJSONfromURL(params[0]);
					JSONArray data_chat_list = json.getJSONArray("data_chat_list");
				
					for (int i = 0; i < data_chat_list.length(); i++){
						JSONObject jsonobj = data_chat_list.getJSONObject(i);
						
						HashMap<String, String> map = new HashMap<String, String>();

						map.put("dari",  jsonobj.getString("dari"));
			        	map.put("msg", jsonobj.getString("msg"));
			        	map.put("cdate", jsonobj.getString("cdate").substring(0, jsonobj.getString("cdate").length()-3));
			        	mylist.add(map);
					}
				} catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
				return null;
			}
			
			
			protected void onPostExecute(ArrayList<HashMap<String, String>> result) {
				
				ListAdapter adapters = new SimpleAdapter(ChatPrivate.this, mylist , 
						R.layout.data_chat_private,
						new String[] { "dari", "msg", "cdate"},
						new int[] { R.id.txt_nama_chat, R.id.txt_isi_chat, R.id.txt_cdate_chat});
				setListAdapter(adapters);
				
				Intent in = getIntent();
				String nama = in.getStringExtra("to_nama");
				
				to_nama = (TextView) findViewById(R.id.txt_to_private);
				to_nama.setText("Chat to: " + nama);		
			}
		}
		
		class getChatPrivate extends Thread {
			ListActivity la;
			showChatList sc;
			
			public getChatPrivate(ListActivity l, showChatList s){
				
				la = l;
				sc = s;
			}
		
			public void run() {
				// TODO Auto-generated method stub
				Intent in = la.getIntent();
				String ID_USER_1 = in.getStringExtra("id_user");
				String ID_1 = in.getStringExtra("id");
				while(true){
					showChatList sh = new showChatList();
					sh.execute("http://192.168.1.73:8080/chat/data_chat_list.php?id_user="+ID_USER_1+"&id="+ID_1);
					
					try {
						Thread.sleep(2000);
					} catch (InterruptedException e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
				}
			}
		}
		
	
		
}
